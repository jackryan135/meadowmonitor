#include "Adafruit_seesaw.h"

#define MOISTURE_PIN 36
#define LIGHT_PIN 15

Adafruit_seesaw ss;
 
void setup() {
  Serial.begin(115200);
  pinMode(LIGHT_PIN, INPUT);
  pinMode(MOISTURE_PIN, INPUT);

  if (!ss.begin(0x36)) {
    Serial.println("ERROR: soil sensor not found");
    while (1);
  } else {
    Serial.print("seesaw started! version: ");
    Serial.println(ss.getVersion(), HEX);
  }
}
 
void loop() {
  int light = analogRead(LIGHT_PIN);
  int moisture = analogRead(MOISTURE_PIN);
  
  // (0°C × 9/5) + 32
  float temp = (ss.getTemp() * (9.0/5.0)) + 32.0;

  Serial.print("Light: ");
  Serial.println(light);

  Serial.print("Moisture: ");
  Serial.println(moisture);

  Serial.print("Temperature (F): ");
  Serial.println(temp);
  
  delay(2000);
}
