#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <DHT.h>
#include <MQ135.h>

#include <UniversalTelegramBot.h>   
#include <ArduinoJson.h>

#define DHTPIN D3
#define DHTTYPE DHT22
#define PIN_MQ135 A0

DHT dht(DHTPIN, DHTTYPE);
MQ135 gasSensor = MQ135(PIN_MQ135);

#define CHAT_ID "826634550"
#define BOTtoken "7466435237:AAFE0uHD5iuGDG_Q1MmuDT-sRhankfc95PA"

X509List cert(TELEGRAM_CERTIFICATE_ROOT);
WiFiClientSecure client;
UniversalTelegramBot bot(BOTtoken, client);
//Checks for new messages every 1 second.
int botRequestDelay = 1000;
unsigned long lastTimeBotRan;


const char* ssid     = "your SSID";
const char* password = "your password";

const char* SERVER_NAME = "your domain/sensordata.php";
String PROJECT_API_KEY = "tempQuality";

unsigned long lastMillis = 0;
long interval = 30000; // interval in milliseconds (30 seconds)

String getReadings(){
  float temperature, humidity, gas;
  temperature = dht.readTemperature();
  humidity = dht.readHumidity();
  gas = gasSensor.getCorrectedPPM(temperature, humidity);
  String message = "Temperature: " + String(temperature) + " ºC ";
  if(temperature<22.29)
    message += "(Too Cold) \n";
  else if(temperature>=22.29 && temperature<26.51)
    message += "(Cold) \n";
  else if(temperature>=26.51 && temperature<30.79)
    message += "(Normal) \n";
  else
    message += "(Hot) \n";
  message += "Humidity: " + String (humidity) + " % ";
  if(humidity<72.0)
    message += "(Low) \n";
  else if(humidity>=72.0 && humidity<87.0)
    message += "(Normal) \n";
  else
    message += "(High) \n";
  message += "C02 Concentration: " + String (gas) + " ppm \n";
  return message;
}

float gasReadings[2] = {0.0, 0.0}; // Array to store the two latest gas readings

String alertGas() {
    float temperature, humidity;
    temperature = dht.readTemperature();
    humidity = dht.readHumidity();

    // Read latest gas sensor reading
    float latestGas = gasSensor.getCorrectedPPM(temperature, humidity);

    // Shift previous reading to index 1
    gasReadings[1] = gasReadings[0];

    // Store latest reading at index 0
    gasReadings[0] = latestGas;

    // Calculate the difference between the two gas readings
    float diffgas = gasReadings[1] - gasReadings[0];

    // Prepare the message based on gas difference
    String message = "";
    if (diffgas > 40) {
        message += "Alert! CO2 dropping!";
    }

    return message;
}



//Handle what happens when you receive new messages
void handleNewMessages(int numNewMessages) {
  Serial.println("handleNewMessages");
  Serial.println(String(numNewMessages));

  for (int i=0; i<numNewMessages; i++) {
    // Chat id of the requester
    String chat_id = String(bot.messages[i].chat_id);
    if (chat_id != CHAT_ID){
      bot.sendMessage(chat_id, "Unauthorized user", "");
      continue;
    }
    
    // Print the received message
    String text = bot.messages[i].text;
    Serial.println(text);

    String from_name = bot.messages[i].from_name;

    if (text == "/start") {
      String welcome = "Welcome, " + from_name + ".\n";
      welcome += "Use the following command to get current readings.\n\n";
      welcome += "/readings: To check on temp, humidity and C02 \n";
      bot.sendMessage(chat_id, welcome, "");
    }

    if (text == "/readings") {
      String readings = getReadings();
      bot.sendMessage(chat_id, readings, "");
    } 
  }
}

void setup() {
    Serial.begin(115200);
    
    Serial.println("Connecting to WiFi");
    configTime(0, 0, "pool.ntp.org");      // get UTC time via NTP
    client.setTrustAnchors(&cert); // Add root certificate for api.telegram.org
    dht.begin();

    WiFi.begin(ssid, password);

    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }

    Serial.println("");
    Serial.println("WiFi connected");
    Serial.print("IP address: ");
    Serial.println(WiFi.localIP());
}

void loop() {
    if (WiFi.status() == WL_CONNECTED) {
        if (millis() - lastMillis > interval) {
            float temperature = dht.readTemperature();
            float humidity = dht.readHumidity();

            if (isnan(temperature) || isnan(humidity)) {
                Serial.println("Failed to read from DHT sensor!");
            } else {
                
                float gas_level = gasSensor.getCorrectedPPM(temperature, humidity); 
                // Assuming MQ135 analog output is connected to A0
                //float gas_level = analogRead(A0);
                int air_quality = getAirQuality(gas_level);

                sendSensorData(temperature, humidity, gas_level, air_quality);

                // Check gas levels and send alert if necessary
                String gasAlert = alertGas();
                if (!gasAlert.isEmpty())
                    bot.sendMessage(CHAT_ID, gasAlert, "");
            }

            lastMillis = millis();
        }
    } else {
        Serial.println("WiFi Disconnected");
        WiFi.begin(ssid, password); // Reconnect to WiFi if disconnected
    }

    delay(2000); // Delay between sensor readings
  if (millis() > lastTimeBotRan + botRequestDelay)  {
    int numNewMessages = bot.getUpdates(bot.last_message_received + 1);

    while(numNewMessages) {
      Serial.println("got response");
      handleNewMessages(numNewMessages);
      numNewMessages = bot.getUpdates(bot.last_message_received + 1);
    }
    lastTimeBotRan = millis();
  }
}

int getAirQuality(float gas_level) {
    // Implement your logic to determine air quality based on gas level
    // Example logic:
    if (gas_level <=1000) {
        return 1; // High
    } else if (gas_level >1000 && gas_level<=2000) {
        return 2; // Moderate
    } else {
        return 3; // Low
    }
}



void sendSensorData(float temperature, float humidity, float gas_level, int air_quality) {
    WiFiClient client;
    HTTPClient http;

    String postData = "api_key=" + PROJECT_API_KEY;
    postData += "&temperature=" + String(temperature, 2);
    postData += "&humidity=" + String(humidity, 2);
    postData += "&gas_level=" + String(gas_level, 2);
    postData += "&air_quality=" + String(air_quality);

    http.begin(client, SERVER_NAME);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    int httpResponseCode = http.POST(postData);

    if (httpResponseCode > 0) {
        Serial.print("HTTP Response code: ");
        Serial.println(httpResponseCode);
    } else {
        Serial.print("Error in HTTP request: ");
        Serial.println(httpResponseCode);
    }

    http.end();
}
