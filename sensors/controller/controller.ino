#include <Arduino.h>
#include "Adafruit_seesaw.h"

// timing
#define N 10000 // Update ESP every 10 seconds
#define M 5000 // Update server every 5 seconds
#define WATER_TIME 2000

// Default values
#define TEMP 72 // Default temperature
#define MOISTURE 2900 // Default moisture
#define LIGHT 2500 // Default light; TODO: find default for light

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

uint16_t readMoisture(int n);
float readTemp(int n);

Adafruit_seesaw ss;

unsigned long n_time;
unsigned long m_time;

struct preferences {
  char dev_id[100];
  float temperature;
  int moisture;
  int light;
} user_prefs;

void setup() {
  // put your setup code here, to run once:
  Serial.begin(115200);

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
  unsigned long current_time = millis();
  if (current_time - n_time >= N) {
    // Update ESP from server
    // => struct user_prefs
    // Data will come back as JSON
    // Some values will be LOW-HIGH -> convert to analog values via sensor ranges
    n_time = current_time;
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
