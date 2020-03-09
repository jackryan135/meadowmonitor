#define MOISTURE_PIN 36
 
void setup() {
  Serial.begin(115200);
  pinMode(MOISTURE_PIN, INPUT);
}
 
void loop() {
  int moisture = analogRead(MOISTURE_PIN);
  Serial.println(moisture);
  delay(2000);
}
