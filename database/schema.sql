raymond.butac
raymond.butac
Invisible

Michael Phillips — Yesterday at 2:34 PM
I would start the ID at 200
Michael Phillips — Yesterday at 8:44 PM
Sorry to add this to you but we found something that's missing  please see attached.  We are not changing, will wait for you all to add and push.  Thanks
-- =========================
-- Visits + Notes (Extended for Doctor Workflow)
-- =========================

CREATE TABLE `Visit` (
  `Visit_ID` BIGINT NOT NULL AUTO_INCREMENT,

visit notes schema.txt
5 KB
this will be needed for the doctor and nurse
raymond.butac — Yesterday at 8:58 PM
With this it still uses Visit_Notes, I have messaged my team to get started on the creation of the Intake table which would include visit note information as well as the intake columns from the nurse apis. With that being said the intake table would just replace visit notes in this schema and we can add the other tables as needed correct?
Michael Phillips — Yesterday at 9:00 PM
give me a sec to fully look this over, won't take long
Michael Phillips — Yesterday at 9:08 PM
Visit = main encounter
Intake = structured nurse intake / vitals / intake note
Visit_Notes = additional doctor/nurse narrative notes over time
later add medication table if needed

Yes, the Intake table can replace the structured intake portion of this schema, and we can add the other tables later as needed. But I do not want to remove Visit_Notes unless we are sure we only need one note record per visit. If we want multiple timestamped notes by different users, Visit_Notes should stay and Intake should handle the nurse intake/vitals side.

Hope this helps
Michael Phillips — Yesterday at 9:39 PM
We can talk more about Tuesday
Michael Phillips — 1:22 PM
We need a few database updates to support the nurse intake feature.

Requested schema changes:

Add a nullable column to the Visit table:

Doctor_Case_Status VARCHAR(20) NULL

Add a new VisitExam table tied 1:1 to Visit by Visit_ID to store:

Nurse_Intake_Note
Doctor_Exam_Note
Blood_Pressure
Pulse
Respiration
Temperature
Oxygen_Saturation
Height
Weight
Pain_Level
Updated_By_User_ID
Updated_At

Add a new VisitMedication table tied 1:1 to Visit by Visit_ID to store:

Current_Medications
Medication_Changes
Medication_Notes
Updated_By_User_ID
Updated_At

Both new tables should reference Visit(Visit_ID) and Users(User_ID) with the same foreign key behavior shown in the updated schema.

There are also 6 new seed appointment rows in the teammate file, but those appear to be optional test/demo data rather than required structural changes.

Added appointment IDs:

3016
3017
3018
3019
3020
3021

Statuses used in those new rows:

SCHEDULED
CHECKED_IN
COMPLETED
CANCELLED
sorry, I'm going to be throwing a lot at you
raymond.butac — 1:33 PM
All good teams working on some new tables currently I will try to get everything caught up for class on Tuesday
I will keep you updated
Michael Phillips — 1:34 PM
Thanks nurse page is way behind and I'm trying to get it caught up
some of this may also help fernando
raymond.butac — 1:40 PM
Got it. I will be out of the house for a good portion of the day so I will get my team to work on it
Whenever i get back home I will grind out these changes
Michael Phillips — 1:40 PM
thanks
raymond.butac — 1:41 PM
No problem
Michael Phillips — 5:20 PM
I have another one for you:
CREATE TABLE Billing (
  Billing_ID BIGINT NOT NULL AUTO_INCREMENT,
  Visit_ID BIGINT NOT NULL,
  Amount DECIMAL(10,2) NOT NULL,
  Status VARCHAR(20) NOT NULL DEFAULT 'UNPAID',
  Created_At DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  Updated_At DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (Billing_ID),
  UNIQUE KEY uk_Billing_Visit_ID (Visit_ID),
  CONSTRAINT fk_Billing_Visit
    FOREIGN KEY (Visit_ID) REFERENCES Visit (Visit_ID)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
raymond.butac — 5:59 PM
Okay I just got home I am working on everything now
raymond.butac — 6:15 PM
Regarding the VisitExam table, it includes information that would be similar to the Intake table. Would this table replace the Intake table?
raymond.butac — 7:11 PM
Here is an updated schema. I did not include an Intakes table due to the addition of the Visit_Exam table. If that is something you would still like to implement we can talk about it and I can work it out. I have also tied Visit with Room so that the Room_ID will be a foreign key to the Visit table. Other than that, everything else I believe is added like you sent. Let me know your thoughts and if it is good I will create a pull request
DROP DATABASE IF EXISTS riversideclinicdb;
CREATE DATABASE riversideclinicdb;
USE riversideclinicdb;

SET NAMES utf8mb4;

message.txt
13 KB
Also if you would like a seed to be created to test all of the new tables and columns let me know
Michael Phillips — 7:25 PM
Did you add the billing’s one I sent you?
Other than that I’m good
raymond.butac — 7:26 PM
Yes
Michael Phillips — 7:27 PM
Awesome then I’m okay for now
I will have more
raymond.butac — 7:36 PM
Okay should I make the pull request now or wait to see if you have more updates/chnages?
Michael Phillips — 7:37 PM
No go ahead. I have to build the code first
raymond.butac — 7:37 PM
Got it I will make the pull tonight
﻿
Michael Phillips
redneck514
 
DROP DATABASE IF EXISTS riversideclinicdb;
CREATE DATABASE riversideclinicdb;
USE riversideclinicdb;

SET NAMES utf8mb4;

-- =========================
-- Core Access Control
-- =========================

CREATE TABLE `Roles` (
  `Role_ID` BIGINT NOT NULL AUTO_INCREMENT,
  `Role_Name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`Role_ID`),
  UNIQUE KEY `uk_Roles_Role_Name` (`Role_Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Permissions` (
  `Role_ID` BIGINT NOT NULL,
  `Table_Name` VARCHAR(50) NOT NULL,
  `Can_View` BOOLEAN NOT NULL DEFAULT FALSE,
  `Can_Edit` BOOLEAN NOT NULL DEFAULT FALSE,
  `Can_Delete` BOOLEAN NOT NULL DEFAULT FALSE,
  `Can_Add` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`Role_ID`, `Table_Name`),
  CONSTRAINT `fk_Permissions_Roles`
    FOREIGN KEY (`Role_ID`) REFERENCES `Roles` (`Role_ID`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- Users + Login
-- =========================

CREATE TABLE `Users` (
  `User_ID` BIGINT NOT NULL AUTO_INCREMENT,
  `First_Name` VARCHAR(128) NOT NULL,
  `Last_Name` VARCHAR(128) NOT NULL,
  `Role_ID` BIGINT NOT NULL,
  `Phone_Number` VARCHAR(20) NULL,
  `Email` VARCHAR(255) NOT NULL,
  `Is_Disabled` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`User_ID`),
  UNIQUE KEY `uk_User_Email` (`Email`),
  KEY `idx_User_Role_ID` (`Role_ID`),
  CONSTRAINT `fk_User_Roles`
    FOREIGN KEY (`Role_ID`) REFERENCES `Roles` (`Role_ID`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `User_Login_Info` (
  `User_ID` BIGINT NOT NULL,
  `Username` VARCHAR(64) NOT NULL,
  `Password_Hash` VARCHAR(255) NOT NULL,
  `Must_Change_Password` TINYINT(1) NOT NULL DEFAULT 1,
  `Password_Changed_At` DATETIME NULL,
  `Created_At` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`User_ID`),
  UNIQUE KEY `uk_User_Login_Info_Username` (`Username`),
  CONSTRAINT `fk_User_Login_Info_User`
    FOREIGN KEY (`User_ID`) REFERENCES `Users` (`User_ID`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- Patients + Related
-- =========================

CREATE TABLE `Patient` (
  `Patient_ID` BIGINT NOT NULL AUTO_INCREMENT,
  `First_Name` VARCHAR(128) NOT NULL,
  `Last_Name` VARCHAR(128) NOT NULL,
  `Phone_Number` VARCHAR(20) NULL,
  `Email` VARCHAR(255) NULL,
  `Date_Of_Birth` DATE NOT NULL,
  PRIMARY KEY (`Patient_ID`),
  KEY `idx_Patient_Last_Name` (`Last_Name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Emergency_Contact` (
  `Emergency_Contact_ID` BIGINT NOT NULL AUTO_INCREMENT,
  `First_Name` VARCHAR(128) NOT NULL,
  `Last_Name` VARCHAR(128) NOT NULL,
  `Phone_Number` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`Emergency_Contact_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Patient_Emergency_Contacts` (
  `Patient_ID` BIGINT NOT NULL,
  `Emergency_Contact_ID` BIGINT NOT NULL,
  `Relationship_To_Patient` VARCHAR(128) NOT NULL,
  PRIMARY KEY (`Patient_ID`, `Emergency_Contact_ID`),
  KEY `idx_PEC_Emergency_Contact_ID` (`Emergency_Contact_ID`),
  CONSTRAINT `fk_PEC_Patient`
    FOREIGN KEY (`Patient_ID`) REFERENCES `Patient` (`Patient_ID`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_PEC_Emergency_Contact`
    FOREIGN KEY (`Emergency_Contact_ID`) REFERENCES `Emergency_Contact` (`Emergency_Contact_ID`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Insurance_Info` (
  `Insurance_ID` BIGINT NOT NULL AUTO_INCREMENT,
  `Patient_ID` BIGINT NOT NULL,
  `Insurance_Provider` VARCHAR(255) NOT NULL,
  `Payment_Status` VARCHAR(30) NOT NULL,
  `Date_Sent` DATE NOT NULL,
  PRIMARY KEY (`Insurance_ID`),
  KEY `idx_Insurance_Info_Patient_ID` (`Patient_ID`),
  CONSTRAINT `fk_Insurance_Info_Patient`
    FOREIGN KEY (`Patient_ID`) REFERENCES `Patient` (`Patient_ID`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- Rooms
-- =========================

CREATE TABLE `Room` (
  `Room_ID` BIGINT NOT NULL AUTO_INCREMENT,
  `Room_Number` VARCHAR(20) NOT NULL,
  `Room_Status` VARCHAR(20) NOT NULL DEFAULT 'AVAILABLE',
  PRIMARY KEY (`Room_ID`),
  UNIQUE KEY `uk_Room_Room_Number` (`Room_Number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- Scheduling
-- =========================

CREATE TABLE `Provider_Schedule` (
  `Schedule_ID` BIGINT NOT NULL AUTO_INCREMENT,
  `Provider_User_ID` BIGINT NOT NULL,
  `Day_Of_The_Week` TINYINT NOT NULL,
  `Start_Time` TIME NOT NULL,
  `End_Time` TIME NOT NULL,
  PRIMARY KEY (`Schedule_ID`),
  KEY `idx_Provider_Schedule_Provider_User_ID` (`Provider_User_ID`),
  CONSTRAINT `fk_Provider_Schedule_Provider`
    FOREIGN KEY (`Provider_User_ID`) REFERENCES `Users` (`User_ID`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `chk_Provider_Schedule_Day_Of_The_Week`
    CHECK (`Day_Of_The_Week` BETWEEN 1 AND 7),
  CONSTRAINT `chk_Provider_Schedule_Time_Range`
    CHECK (`Start_Time` < `End_Time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Appointment` (
  `Appointment_ID` BIGINT NOT NULL AUTO_INCREMENT,
  `Patient_ID` BIGINT NOT NULL,
  `Provider_User_ID` BIGINT NOT NULL,
  `Scheduled_Start` DATETIME NOT NULL,
  `Scheduled_End` DATETIME NOT NULL,
  `Status` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`Appointment_ID`),
  KEY `idx_Appointment_Patient_ID` (`Patient_ID`),
  KEY `idx_Appointment_Provider_User_ID` (`Provider_User_ID`),
  KEY `idx_Appointment_Provider_Time` (`Provider_User_ID`, `Scheduled_Start`, `Scheduled_End`),
  CONSTRAINT `fk_Appointment_Patient`
    FOREIGN KEY (`Patient_ID`) REFERENCES `Patient` (`Patient_ID`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_Appointment_Provider`
    FOREIGN KEY (`Provider_User_ID`) REFERENCES `Users` (`User_ID`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `chk_Appointment_Time_Range`
    CHECK (`Scheduled_Start` < `Scheduled_End`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- Visits + Notes + Workflow
-- =========================

CREATE TABLE `Visit` (
  `Visit_ID` BIGINT NOT NULL AUTO_INCREMENT,
  `Created_By_User_ID` BIGINT NOT NULL,
  `Appointment_ID` BIGINT NULL,
  `Patient_ID` BIGINT NOT NULL,
  `Provider_User_ID` BIGINT NOT NULL,
  `Room_ID` BIGINT NULL,
  `Visit_Date_Time` DATETIME NOT NULL,
  `Doctor_Case_Status` VARCHAR(20) NULL,
  PRIMARY KEY (`Visit_ID`),
  KEY `idx_Visit_Created_By_User_ID` (`Created_By_User_ID`),
  KEY `idx_Visit_Appointment_ID` (`Appointment_ID`),
  KEY `idx_Visit_Patient_ID` (`Patient_ID`),
  KEY `idx_Visit_Provider_User_ID` (`Provider_User_ID`),
  KEY `idx_Visit_Room_ID` (`Room_ID`),
  CONSTRAINT `fk_Visit_Created_By_User`
    FOREIGN KEY (`Created_By_User_ID`) REFERENCES `Users` (`User_ID`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_Visit_Appointment`
    FOREIGN KEY (`Appointment_ID`) REFERENCES `Appointment` (`Appointment_ID`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_Visit_Patient`
    FOREIGN KEY (`Patient_ID`) REFERENCES `Patient` (`Patient_ID`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_Visit_Provider`
    FOREIGN KEY (`Provider_User_ID`) REFERENCES `Users` (`User_ID`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_Visit_Room`
    FOREIGN KEY (`Room_ID`) REFERENCES `Room` (`Room_ID`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Visit_Notes` (
  `Note_ID` BIGINT NOT NULL AUTO_INCREMENT,
  `Visit_ID` BIGINT NOT NULL,
  `Created_By_User_ID` BIGINT NOT NULL,
  `Visit_Note` TEXT NOT NULL,
  `Note_Date_Time` DATETIME NOT NULL,
  PRIMARY KEY (`Note_ID`),
  KEY `idx_Visit_Notes_Visit_ID` (`Visit_ID`),
  KEY `idx_Visit_Notes_Created_By_User_ID` (`Created_By_User_ID`),
  CONSTRAINT `fk_Visit_Notes_Visit`
    FOREIGN KEY (`Visit_ID`) REFERENCES `Visit` (`Visit_ID`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_Visit_Notes_Created_By_User`
    FOREIGN KEY (`Created_By_User_ID`) REFERENCES `Users` (`User_ID`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Visit_Exam` (
  `Visit_ID` BIGINT NOT NULL,
  `Nurse_Intake_Note` TEXT NULL,
  `Doctor_Exam_Note` TEXT NULL,
  `Blood_Pressure` VARCHAR(30) NULL,
  `Pulse` VARCHAR(20) NULL,
  `Respiration` VARCHAR(20) NULL,
  `Temperature` VARCHAR(20) NULL,
  `Oxygen_Saturation` VARCHAR(20) NULL,
  `Height` VARCHAR(20) NULL,
  `Weight` VARCHAR(20) NULL,
  `Pain_Level` VARCHAR(20) NULL,
  `Updated_By_User_ID` BIGINT NULL,
  `Updated_At` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`Visit_ID`),
  KEY `idx_Visit_Exam_Updated_By_User_ID` (`Updated_By_User_ID`),
  CONSTRAINT `fk_Visit_Exam_Visit`
    FOREIGN KEY (`Visit_ID`) REFERENCES `Visit` (`Visit_ID`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_Visit_Exam_Updated_By_User`
    FOREIGN KEY (`Updated_By_User_ID`) REFERENCES `Users` (`User_ID`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Visit_Medication` (
  `Visit_ID` BIGINT NOT NULL,
  `Current_Medications` TEXT NULL,
  `Medication_Changes` TEXT NULL,
  `Medication_Notes` TEXT NULL,
  `Updated_By_User_ID` BIGINT NULL,
  `Updated_At` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`Visit_ID`),
  KEY `idx_Visit_Medication_Updated_By_User_ID` (`Updated_By_User_ID`),
  CONSTRAINT `fk_Visit_Medication_Visit`
    FOREIGN KEY (`Visit_ID`) REFERENCES `Visit` (`Visit_ID`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_Visit_Medication_Updated_By_User`
    FOREIGN KEY (`Updated_By_User_ID`) REFERENCES `Users` (`User_ID`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- Billing
-- =========================

CREATE TABLE `Billing` (
  `Billing_ID` BIGINT NOT NULL AUTO_INCREMENT,
  `Visit_ID` BIGINT NOT NULL,
  `Amount` DECIMAL(10,2) NOT NULL,
  `Status` VARCHAR(20) NOT NULL DEFAULT 'UNPAID',
  `Created_At` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Updated_At` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`Billing_ID`),
  UNIQUE KEY `uk_Billing_Visit_ID` (`Visit_ID`),
  CONSTRAINT `fk_Billing_Visit`
    FOREIGN KEY (`Visit_ID`) REFERENCES `Visit` (`Visit_ID`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- Assignments + Auditing
-- =========================

CREATE TABLE `Nurse_Assignments` (
  `Assignment_ID` BIGINT NOT NULL AUTO_INCREMENT,
  `Nurse_User_ID` BIGINT NOT NULL,
  `Patient_ID` BIGINT NOT NULL,
  PRIMARY KEY (`Assignment_ID`),
  KEY `idx_Nurse_Assignments_Nurse_User_ID` (`Nurse_User_ID`),
  KEY `idx_Nurse_Assignments_Patient_ID` (`Patient_ID`),
  CONSTRAINT `fk_Nurse_Assignments_Nurse`
    FOREIGN KEY (`Nurse_User_ID`) REFERENCES `Users` (`User_ID`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_Nurse_Assignments_Patient`
    FOREIGN KEY (`Patient_ID`) REFERENCES `Patient` (`Patient_ID`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `Audit_Log` (
  `Audit_Log_ID` BIGINT NOT NULL AUTO_INCREMENT,
  `User_ID` BIGINT NOT NULL,
  `Audit_Date` DATETIME NOT NULL,
  `Action_Type` VARCHAR(30) NOT NULL,
  `Entity_Name` VARCHAR(50) NOT NULL,
  `Entity_Record_ID` BIGINT NOT NULL,
  `Details` TEXT NULL,
  PRIMARY KEY (`Audit_Log_ID`),
  KEY `idx_Audit_Log_User_ID` (`User_ID`),
  CONSTRAINT `fk_Audit_Log_User`
    FOREIGN KEY (`User_ID`) REFERENCES `Users` (`User_ID`)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;