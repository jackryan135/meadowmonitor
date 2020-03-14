#define HEAT_PIN 2

void setup() {
  // put your setup code here, to run once:
  Serial.begin(115200);
  pinMode(HEAT_PIN, OUTPUT);
  digitalWrite(HEAT_PIN, LOW);
  
}

void loop() {
  // put your main code here, to run repeatedly:
  digitalWrite(HEAT_PIN, HIGH);
  delay(120000);
  digitalWrite(HEAT_PIN, LOW);
  delay(120000);
}
