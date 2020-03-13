#include <WiFi.h>
#include <WiFiClient.h>
#include <WebServer.h>
#include <HTTPClient.h>
 
const char* ssid = "SCU-Student";
const char* password =  "gosantaclara";

const String deviceURL = "http://meadowmonitor.com:5001/api/emb/";
const String addURL = "http://meadowmonitor.com:5001/api/webapp/";
 
void setup() {
 
  Serial.begin(115200);
  delay(4000);   //Delay needed before calling the WiFi.begin
 
  WiFi.begin(ssid, password); 
  
  while (WiFi.status() != WL_CONNECTED) { //Check for the connection
    delay(1000);
    Serial.println("Connecting to WiFi..");
  }
 
  Serial.println("Connected to the WiFi network");
 
}

void getDesired(int devID){

  HTTPClient desiredHTTP;
  //devID = 6;
  String desiredURL = deviceURL + devID; 
  desiredURL.concat("/desired/");
  desiredHTTP.begin(desiredURL);
  desiredHTTP.addHeader("Content-Type","application/json");

  String getInfo = String(devID);
  
  
  Serial.println(getInfo);
  int sendRC = desiredHTTP.GET();
  if (sendRC > 0){
    String response = desiredHTTP.getString();                       //Get the response to the request
 
    Serial.println(sendRC);   //Print return code
    Serial.println(response);

  }else{
 
    Serial.print("Error on sending POST: ");
    Serial.println(sendRC);
 
   }
   desiredHTTP.end();
    
}//fetch desired values

void addDevice(String devName, int userID){
  HTTPClient deviceHTTP;
  String devNameURL = addURL + userID;
  devNameURL.concat("/add");

  deviceHTTP.begin(devNameURL);
  deviceHTTP.addHeader("Content-Type","application/json");

  String putInfo =  "{\"label\": \"";
  /*String ds = String(devID);*/
  putInfo.concat(devName);
  putInfo.concat("\"}");
  Serial.println(putInfo);
  int sendRC = deviceHTTP.PUT(putInfo);
  if (sendRC > 0){
    String response = deviceHTTP.getString();                       //Get the response to the request
 
    Serial.println(sendRC);   //Print return code
    Serial.println(response);

  }else{
 
    Serial.print("Error on sending PUT: ");
    Serial.println(sendRC);
 
   }
   deviceHTTP.end();
  

 
}



void sendInfo(int devID, int lightVal, int moistVal, int tempVal, int phVal){
  HTTPClient sendHTTP;
  //devID = 6;
  String sendURL = deviceURL + devID + "/log/";
  sendHTTP.begin(sendURL);
  sendHTTP.addHeader("Content-Type","application/json");

/*   int lightVal;
  int moistVal;
  int phVal = 0;
  int tempVal; */

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
    String response = sendHTTP.getString();                       //Get the response to the request
 
    Serial.println(sendRC);   //Print return code
    Serial.println(response);

  }else{
 
    Serial.print("Error on sending POST: ");
    Serial.println(sendRC);
 
   }
   sendHTTP.end();
}//end send info

/* void getDesired(){}

void processValues(){} //add mikes stuff here the actuator shit

  */
void loop() {
 
 if(WiFi.status()== WL_CONNECTED){   //Check WiFi connection status
   /*sendInfo(6,6,6,6,6);
   String devName1 = "marijuana";
   addDevice(devName1,6); */
   getDesired(1);
   /* HTTPClient http;   
   //String mmURL = "http://www.meadowmonitor.com:5001/api/webapp/6/add";
   String mmURLlist = "http://meadowmonitor.com:5001/api/webapp/6";
   String desiredValuesURL ="http://meadowmonitor.com:5001/api/emb/6/desired";
   
   http.begin(mmURLlist);  //Specify destination for HTTP request
   http.addHeader("Content-Type", "application/json");             //Specify content-type header
 
   //int httpResponseCode = http.PUT("POSTING from ESP32");   //Send the actual POST request
   int httpResponseCode = http.GET();
   if(httpResponseCode>0){
 
    String response = http.getString();                       //Get the response to the request
 
    Serial.println(httpResponseCode);   //Print return code
    Serial.println(response);           //Print request answer
 
   }else{
 
    Serial.print("Error on sending POST: ");
    Serial.println(httpResponseCode);
 
   }
 
   http.end();  //Free resources 
 */
 } else{
 
    Serial.println("Error in WiFi connection");   
 
 }
 
  delay(10000);  //Send a request every 10 seconds
 
}
