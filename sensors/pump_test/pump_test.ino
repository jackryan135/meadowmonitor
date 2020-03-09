#define WATER_EN 13
#define WATER_PIN1 12
#define WATER_PIN2 14

#define WATER_TIME 2000

void setup() {
  // put your setup code here, to run once:
  Serial.begin(115200);

  pinMode(WATER_EN, OUTPUT);
  pinMode(WATER_PIN1, OUTPUT);
  pinMode(WATER_PIN2, OUTPUT);
}

void loop() {
  // put your main code here, to run repeatedly:
  unsigned long start_time = millis();
  unsigned long current_time = millis();

  // Turn on pump
  digitalWrite(WATER_EN, HIGH);
  digitalWrite(WATER_PIN1, LOW);
  digitalWrite(WATER_PIN2, HIGH);

  Serial.println("here");
  while (current_time - start_time < WATER_TIME) {
    current_time = millis();
  }
  Serial.println("finish watering");

  // Turn off pump
  digitalWrite(WATER_PIN1, LOW);
  digitalWrite(WATER_PIN2, LOW);


  delay(10000);
}
