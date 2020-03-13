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
#define MOISTURE 2900 // Default moisture
#define LIGHT 2500 // Default light

// Sensor ranges
#define MOIST_HIGH 3050
#define MOIST_MED 2900
#define MOIST_LOW 2750

#define LIGHT_HIGH 3800
#define LIGHT_MED 3300
#define LIGHT_LOW 2800

// inputs
#define MOISTURE_PIN 36
#define LIGHT_PIN 15

// outputs
#define HEAT_PIN 2
#define WATER_EN 35
#define WATER_PIN1 32
#define WATER_PIN2 33

float readTemp(int n);
void sendInfo(int devID, char *lightVal, char *moistVal, char *tempVal);
String *getDesired(int devID);

const char *portal_ssid = "meadow-monitor-esp32";
const char *portal_password = NULL;  // no password

const String deviceURL = "http://meadowmonitor.com:5001/api/emb/";
const String addURL = "http://meadowmonitor.com:5001/api/webapp/";

Adafruit_seesaw ss;

WebServer server(80);

unsigned long n_time;
unsigned long m_time;

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

  delay(4000);   //Delay needed before calling the WiFi.begin
 
//  WiFi.begin(ssid, password); 
//  
//  while (WiFi.status() != WL_CONNECTED) { //Check for the connection
//    delay(1000);
//    Serial.println("Connecting to WiFi..");
//  }

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
    Serial.print("Connected to ");
    Serial.println(WiFi.SSID());
    
    unsigned long current_time = millis();
    if (current_time - n_time >= N) {
      // Update ESP from server
      // => struct user_prefs
      // Some values will be LOW-HIGH -> convert to analog values via sensor ranges
      n_time = current_time;
  
      String *infoParse = getDesired(user_prefs.dev_id);
      user_prefs.temperature = infoParse[0].toFloat();
  
      String moisture_str = infoParse[1];
      if (moisture_str.equalsIgnoreCase("HIGH"))
        user_prefs.moisture = MOIST_HIGH;
      else if (moisture_str.equalsIgnoreCase("LOW"))
        user_prefs.moisture = MOIST_LOW;
      else user_prefs.moisture = MOIST_MED;
    }
  
    
    if (current_time - m_time >= M) {
      // Update server
      m_time = current_time;
      
      // Read sensors, use actuators as necessary
      float temp = (readTemp(20) * (9.0/5.0)) + 32.0;
      int moisture = analogRead(MOISTURE_PIN);
      int light = analogRead(LIGHT_PIN);
  
  
      if (temp < user_prefs.temperature){
        Serial.println("heat: HIGH");
        digitalWrite(HEAT_PIN, HIGH);
      }
      else {
        Serial.println("heat: LOW");
        digitalWrite(HEAT_PIN, LOW);
      }
  
      if (moisture < user_prefs.moisture) {
        unsigned long start_time = millis();
        digitalWrite(WATER_EN, HIGH);
        digitalWrite(WATER_PIN1, LOW);
        digitalWrite(WATER_PIN2, HIGH);
        
        current_time = millis();
        while (current_time - start_time < WATER_TIME){
          current_time = millis();
        }
        
        digitalWrite(WATER_EN, LOW);
        digitalWrite(WATER_PIN1, LOW);
        digitalWrite(WATER_PIN2, LOW);
          
      }
      Serial.print("Temperature (F): ");
      Serial.println(temp);
  
      // Send moisture, temp, and light to server
      char *moisture_str;
  
      if (moisture > MOIST_HIGH)
        moisture_str = "HIGH";
      else if (moisture < MOIST_LOW)
        moisture_str = "LOW";
      else moisture_str = "MEDIUM";
  
      char *light_str;
      if (light > LIGHT_HIGH)
        light_str = "HIGH";
      else if (light < LIGHT_LOW)
        light_str = "LOW";
      else light_str = "MEDIUM";
  
      char temp_str[10];
      snprintf(temp_str, sizeof(temp_str), "%f", temp);
      sendInfo(user_prefs.dev_id, light_str, moisture_str, temp_str);
      
    }
  }
  
}

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

void sendInfo(int devID, char *lightVal, char *moistVal, char *tempVal) {
  HTTPClient sendHTTP;
  String sendURL = deviceURL + devID + "/log/";
  sendHTTP.begin(sendURL);
  sendHTTP.addHeader("Content-Type","application/json");
  
  int phVal = 0;
  String postInfo = "{\"light\": ";
  
  postInfo.concat(lightVal);
  postInfo.concat(",\n\"moisture\": ");
  postInfo.concat(moistVal);
  postInfo.concat(",\n\"ph\": ");
  postInfo.concat(phVal);
  postInfo.concat(",\n\"temp\": ");
  postInfo.concat(tempVal);
  postInfo.concat("}");
  
  Serial.println(postInfo);
  int sendRC = sendHTTP.POST(postInfo);
  if (sendRC > 0){
    String response = sendHTTP.getString(); //Get the response to the request
 
    Serial.println(sendRC);   //Print return code
    Serial.println(response);

  }else{
 
    Serial.print("Error on sending POST: ");
    Serial.println(sendRC);
 
   }
   sendHTTP.end();
}//end send info

String *getDesired(int devID) {
  HTTPClient desiredHTTP;
  String desiredURL = deviceURL + devID; 
  desiredURL.concat("/desired/");
  desiredHTTP.begin(desiredURL);
  desiredHTTP.addHeader("Content-Type","application/json");

  String getInfo = String(devID);
  
  
  Serial.println(getInfo);
  int sendRC = desiredHTTP.GET();
  String infoParse[2];
  if (sendRC > 0){
    String response = desiredHTTP.getString(); //Get the response to the request
    DynamicJsonDocument info(1024);
    deserializeJson(info,response);

    String temp_str = info["temperature_min"];
    String moist_str = info["moisture"];
    
    Serial.println(sendRC);   //Print return code
    
    infoParse[0] = temp_str;
    infoParse[1] = moist_str;

    Serial.println(infoParse[0]);
    Serial.println(infoParse[1]);

  }else{
 
    Serial.print("Error on sending POST: ");
    Serial.println(sendRC);
  
  }
  desiredHTTP.end();

  return infoParse;
}
