#include <WiFi.h>
#include <WiFiClient.h>
#include <WebServer.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Arduino.h>
#include "Adafruit_seesaw.h"
#include "SPIFFS.h"

// timing
#define N 10000 // Update ESP every 10 seconds
#define M 5000 // Update server every 5 seconds
#define WATER_TIME 2000

// Default values
#define TEMP 72 // Default temperature
#define MOISTURE 2600//2900 // Default moisture
#define LIGHT 2500 // Default light

// Sensor ranges
#define MOIST_HIGH 3050
#define MOIST_MED 2900
#define MOIST_LOW 2750

#define LIGHT_HIGH 3800
#define LIGHT_MED 3500
#define LIGHT_LOW 2900

// inputs
#define MOISTURE_PIN 36
#define LIGHT_PIN 34

// outputs
#define HEAT_PIN 19
#define WATER_EN 23
#define WATER_PIN1 32
#define WATER_PIN2 33

float readTemp(int n);
void sendInfo(int devID, char *lightVal, char *moistVal, char *tempVal);
DynamicJsonDocument getDesired(int devID);

const char *portal_ssid = "meadow-monitor-esp32";
const char *portal_password = NULL;  // no password

const String deviceURL = "http://www.meadowmonitor.com:5001/api/emb/";
const String addURL = "http://www.meadowmonitor.com:5001/api/webapp/";

Adafruit_seesaw ss;

WebServer server(80);

unsigned long n_time;
unsigned long m_time;

float temp_avg = 0.0;

struct preferences {
  int dev_id;
  float temperature;
  int moisture;
  int light;
} user_prefs;

void handle_root() {
  Serial.println("handling root");
  // display form
  File form = SPIFFS.open("/portal.html", "r");
  server.streamFile(form, "text/html");
  form.close();
}

void handle_setup() {
  // todo
  String ssid = server.arg("ssid");
  String password = server.arg("password");
  user_prefs.dev_id = server.arg("device_id").toInt();
  
  Serial.printf("ssid: %s, pw: %s, id: %d\n", ssid.c_str(), password.c_str(), user_prefs.dev_id);
  // Disconnect if already connected.
  if (WiFi.status() == WL_CONNECTED) {
    WiFi.disconnect();
  }

  Serial.printf("connecting to... %s\n", ssid.c_str());
  WiFi.begin(ssid.c_str(), password.c_str());
  // Wait until conncted.
  while (WiFi.status() != WL_CONNECTED) {
    yield();
  }
  
  String response = "<p>Connected to " + WiFi.SSID() + ".</p>\n";
  response += "<p>Click <a href=\"/\">here</a> to change settings again.</p>";
  server.send(200, "text/html", response.c_str());
}

void setup() {
  // put your setup code here, to run once:
  Serial.begin(115200);
  SPIFFS.begin();

  delay(500);   //Delay needed before calling the WiFi.begin

  WiFi.mode(WIFI_AP_STA);
  WiFi.softAP(portal_ssid, portal_password);
  IPAddress myIP = WiFi.softAPIP();
  Serial.print("AP IP address: ");
  Serial.println(myIP);
  server.on("/", handle_root);
  server.on("/setup", HTTP_POST, handle_setup);
  server.begin();
 
  Serial.println("Connected to the WiFi network");

  pinMode(MOISTURE_PIN, INPUT);
  pinMode(LIGHT_PIN, INPUT);

  pinMode(HEAT_PIN, OUTPUT);
  pinMode(WATER_EN, OUTPUT);
  pinMode(WATER_PIN1, OUTPUT);
  pinMode(WATER_PIN2, OUTPUT);

  if (!ss.begin(0x36)) {
    Serial.println("ERROR: soil sensor not found");
    while (1);
  } else {
    Serial.print("seesaw started! version: ");
    Serial.println(ss.getVersion(), HEX);
  }

  n_time = millis();
  m_time = millis();

  user_prefs.temperature = TEMP;
  user_prefs.moisture = MOISTURE;
  user_prefs.light = LIGHT;
}

void loop() {
  // put your main code here, to run repeatedly:

  server.handleClient();

  if (WiFi.status() == WL_CONNECTED) {
    
    unsigned long current_time = millis();

    // Update ESP every 10 seconds
    if (current_time - n_time >= N) {
      Serial.println("UPDATE ESP");
      n_time = current_time;

      // Get data from web server
      DynamicJsonDocument info = getDesired(user_prefs.dev_id);
      String temp_str = info["temperature_min"];
      String moist_str = info["moisture"];

      // Convert to floating point
      if (temp_str.equals("null")) {
        user_prefs.temperature = TEMP;
      } else user_prefs.temperature = temp_str.toFloat();

      // Convert to integer values
      if (moist_str.equals("null")) {
        user_prefs.moisture = MOISTURE;
      } else {
        if (moist_str.equalsIgnoreCase("HIGH"))
          user_prefs.moisture = MOIST_HIGH;
        else if (moist_str.equalsIgnoreCase("LOW"))
          user_prefs.moisture = MOIST_LOW;
        else user_prefs.moisture = MOIST_MED;
      }
    }
  

    // Update server every 5 seconds
    if (current_time - m_time >= M) {
      Serial.println("UPDATE WEB SERVER");
      m_time = current_time;
      
      // Read sensors, use actuators as necessary
      float temp = (readTemp(20) * (9.0/5.0)) + 32.0;
      int moisture = analogRead(MOISTURE_PIN);
      int light = analogRead(LIGHT_PIN);

      // Get rolling average, water pump interferes with readings
      if (temp_avg == 0.0)
        temp = rollingAverage(temp, temp, 5.0);
      else temp = rollingAverage(temp_avg, temp, 5.0);
  

      // Check temperature
      if (temp < user_prefs.temperature){
        digitalWrite(HEAT_PIN, HIGH);
      }
      else {
        digitalWrite(HEAT_PIN, LOW);
      }

      // Check moisture, water if necessary
      if (moisture < user_prefs.moisture) {
        unsigned long start_time = millis();
        digitalWrite(WATER_EN, HIGH);
        digitalWrite(WATER_PIN1, HIGH);
        digitalWrite(WATER_PIN2, LOW);
        
        current_time = millis();
        while (current_time - start_time < WATER_TIME){
          current_time = millis();
        }
        digitalWrite(WATER_EN, LOW);
        digitalWrite(WATER_PIN1, LOW);
        digitalWrite(WATER_PIN2, LOW);
          
      }
  
      // Send moisture, temp, and light to server
      sendInfo(user_prefs.dev_id, light, moisture, temp);
      temp_avg = temp;
    }
  }
  
}

float rollingAverage(float avg, float new_temp, float num_samples) {
  avg -= avg / num_samples;
  avg += new_temp / num_samples;

  return avg;
  
}

// Average temperature readings over n readings
float readTemp(int n) {
  float tempSum;
  for (int i = 0; i < n; i++) {
    float temp = ss.getTemp();
    if (temp == 0.0) {
      i--;
    } else {
      tempSum += temp;
    }
  }

  return (tempSum / (float) n);
}

// Send readings to web server
void sendInfo(int devID, int lightVal, int moistVal, float tempVal) {
  HTTPClient sendHTTP;
  String sendURL = deviceURL + devID + "/log/";
  sendHTTP.begin(sendURL);
  sendHTTP.addHeader("Content-Type","application/json");

  // No ph sensor, set value to 0
  int phVal = 0;

  // Construct POST
  String postInfo = "{\"light\": ";
  postInfo.concat(lightVal);
  postInfo.concat(",\n\"moisture\": ");
  postInfo.concat(moistVal);
  postInfo.concat(",\n\"ph\": ");
  postInfo.concat(phVal);
  postInfo.concat(",\n\"temp\": ");
  postInfo.concat(tempVal);
  postInfo.concat("}");
  
  Serial.println("=========POST=========");
  Serial.println(postInfo);
  Serial.println("=========POST END=========");
  
  int sendRC = sendHTTP.POST(postInfo);
  if (sendRC > 0){
    String response = sendHTTP.getString(); //Get the response to the request

  }else{
 
    Serial.print("Error on sending POST: ");
    Serial.println(sendRC);
 
   }
   sendHTTP.end();
}

// Update ESP with desired values stored on web server
DynamicJsonDocument getDesired(int devID) {
  // Construct GET
  HTTPClient desiredHTTP;
  String desiredURL = deviceURL + devID; 
  desiredURL.concat("/desired/");
  desiredHTTP.begin(desiredURL);
  desiredHTTP.addHeader("Content-Type","application/json");

  String getInfo = String(devID);
  int sendRC = desiredHTTP.GET();
  String infoParse[2];
  DynamicJsonDocument info(1024);
  
  if (sendRC > 0){
    String response = desiredHTTP.getString(); //Get the response to the request
    deserializeJson(info,response);

    Serial.println("=========GET=========");
    Serial.println(response);
    Serial.println("=========END GET=========");

  }else{
 
    Serial.print("Error on sending POST: ");
    Serial.println(sendRC);
  
  }
  desiredHTTP.end();

  // Returns info in JSON format
  return info;
}
