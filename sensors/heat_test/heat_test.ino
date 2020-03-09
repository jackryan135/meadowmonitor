#define HEAT_PIN 34

void setup() {
  // put your setup code here, to run once:
  Serial.begin(115200);
  pinMode(HEAT_PIN, OUTPUT);
}

void loop() {
  // put your main code here, to run repeatedly:
  pinMode(HEAT_PIN, HIGH);
  delay(2000);
  pinMode(HEAT_PIN, LOW);

  delay(10000);
}
