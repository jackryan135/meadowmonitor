#include <Arduino.h>
#include "Adafruit_seesaw.h"

// timing
#define K 10000 // Check sensors every 10 seconds
#define N 3600000 // Update ESP every 1hr
#define M 600000 // Update server every 10 min
#define WATER_TIME 2000

// TODO: change below to real defaults
#define TEMP 50 // Default temperature
#define MOISTURE 400 // Default moisture
#define LIGHT 0 // Default light

// inputs
#define MOISTURE_PIN 36
#define LIGHT_PIN 0

// outputs
#define HEAT_PIN 34
#define WATER_EN 35
#define WATER_PIN1 32
#define WATER_PIN2 33

uint16_t readMoisture(int n);
float readTemp(int n);

Adafruit_seesaw ss;

unsigned long k_time;
unsigned long n_time;
unsigned long m_time;

struct preferences {
  char dev_id[100];
  float temperature;
  uint16_t moisture;
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

  k_time = millis();
  n_time = millis();
  m_time = millis();

  user_prefs.temperature = TEMP;
  user_prefs.moisture = MOISTURE;
  user_prefs.light = LIGHT;
}

void loop() {
  // put your main code here, to run repeatedly:
  unsigned long current_time = millis();

  if (current_time - k_time >= K) {
    // Read sensors, use actuators as necessary
    float temp = readTemp(20);
    int moisture = readMoisture(20);

    if (temp < user_prefs.temperature)
      digitalWrite(HEAT_PIN, HIGH);
    else digitalWrite(HEAT_PIN, LOW);

    if (moisture < user_prefs.moisture) {
      unsigned long start_time = millis();
      digitalWrite(WATER_EN, HIGH);
      digitalWrite(WATER_PIN1, LOW);
      digitalWrite(WATER_PIN2, HIGH);
      
      while (current_time - start_time < WATER_TIME){
        current_time = millis();
      }
      digitalWrite(WATER_EN, LOW);
      digitalWrite(WATER_PIN1, LOW);
      digitalWrite(WATER_PIN2, LOW);
        
    }
      
  }

  if (current_time - n_time >= N) {
    // Update ESP from server
    // => struct user_prefs
    n_time = current_time;
  }

  if (current_time - m_time >= M) {
    // Update server
    m_time = current_time;

    // Transmit these readings to server
    float temp = readTemp(20);
    uint16_t moisture = readMoisture(20);
    // TODO: light??
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

int readMoisture(int n) {
  int moistSum;
  for (int i = 0; i < n; i++) {
    int moist = analogRead(MOISTURE_PIN);
    if (moist == 0) {
      i--;
    } else {
      moistSum += moist;
    }
  }

  return (moistSum / n);
}
