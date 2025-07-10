-- SQL Script for SKYACADEMIA Database
-- DBMS: MySQL
-- Version 2: Enhanced with centralized Users table, OTP tokens, and other improvements.

-- Optional: Drop the database if it already exists.
-- Use with CAUTION in production environments! This will DELETE ALL DATA.
DROP DATABASE IF EXISTS skyacademia_db;

-- 1. Create the Database
CREATE DATABASE IF NOT EXISTS skyacademia_db;

-- Use the newly created database
USE skyacademia_db;

-- --- Core Authentication and Verification Tables ---

-- EmailVerificationTokens Table: For managing OTPs during registration/password reset.
CREATE TABLE IF NOT EXISTS EmailVerificationTokens (
    TokenID INT AUTO_INCREMENT PRIMARY KEY,
    Email VARCHAR(255) NOT NULL,    -- The email address to which the OTP was sent
    OTP VARCHAR(10) NOT NULL,       -- The actual OTP code (e.g., 6-digit number)
    Purpose VARCHAR(50) NOT NULL,   -- e.g., 'Registration', 'PasswordReset'
    ExpiresAt DATETIME NOT NULL,    -- Timestamp when the OTP becomes invalid
    IsUsed BOOLEAN DEFAULT FALSE,   -- Flag to prevent OTP replay attacks
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (Email) -- Index for faster lookups by email
);

-- Users Table: Centralized table for all login accounts (Admins, Teachers, Students, Parents).
-- This streamlines authentication and authorization.
CREATE TABLE IF NOT EXISTS Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Email VARCHAR(255) UNIQUE NOT NULL, -- Unique email for login
    PasswordHash VARCHAR(255) NOT NULL, -- Stores the hashed password (e.g., bcrypt)
    UserRole VARCHAR(50) NOT NULL,      -- 'admin', 'teacher', 'student', 'parent'
    IsActive BOOLEAN DEFAULT TRUE,      -- Account active status
    IsVerified BOOLEAN DEFAULT FALSE,   -- For email verification status (useful for all users)
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (Email), -- Index for faster login lookups
    INDEX (UserRole) -- Index for filtering users by role
);

-- --- Main System Entities ---

-- Schools Table: Details of each registered school.
CREATE TABLE IF NOT EXISTS Schools (
    SchoolID INT AUTO_INCREMENT PRIMARY KEY,
    SchoolName VARCHAR(255) NOT NULL,
    SchoolEmail VARCHAR(255) UNIQUE,    -- Main contact email for the school
    SchoolContacts VARCHAR(255),        -- Main contact number for the school
    ContactPerson VARCHAR(255),         -- Name of the main contact person at the school
    AreaOfLocation VARCHAR(255),
    SchoolStatus VARCHAR(50) DEFAULT 'Pending Verification', -- e.g., 'Active', 'Pending Approval', 'Inactive'
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (SchoolName)
);

-- Administration Table: Profile details for school administrators.
-- Linked to the Users table for authentication.
CREATE TABLE IF NOT EXISTS Administration (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT UNIQUE NOT NULL, -- Foreign Key to Users table for login
    SchoolID INT NOT NULL,
    FirstName VARCHAR(255) NOT NULL,
    LastName VARCHAR(255) NOT NULL,
    Role VARCHAR(100), -- e.g., 'Principal', 'Registrar', 'IT Admin'
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (SchoolID) REFERENCES Schools(SchoolID) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX (SchoolID)
);

-- Teachers Table: Profile details for teachers.
-- Linked to the Users table for authentication.
CREATE TABLE IF NOT EXISTS Teachers (
    TeacherID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT UNIQUE NOT NULL, -- Foreign Key to Users table for login
    SchoolID INT NOT NULL,
    FirstName VARCHAR(255) NOT NULL,
    LastName VARCHAR(255) NOT NULL,
    ContactNumber VARCHAR(20), -- Teachers' direct contact number
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (SchoolID) REFERENCES Schools(SchoolID) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX (SchoolID)
);

-- Courses Table: Academic courses offered by a school.
CREATE TABLE IF NOT EXISTS Courses (
    CourseID INT AUTO_INCREMENT PRIMARY KEY,
    SchoolID INT NOT NULL,
    CourseName VARCHAR(255) NOT NULL,
    CourseDescription TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (SchoolID) REFERENCES Schools(SchoolID) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX (SchoolID, CourseName)
);

-- Classes Table: Specific class sections within a school (e.g., 'Grade 10A', 'Form 4 Science').
CREATE TABLE IF NOT EXISTS Classes (
    ClassID INT AUTO_INCREMENT PRIMARY KEY,
    SchoolID INT NOT NULL,
    ClassName VARCHAR(255) NOT NULL,
    GradeLevel VARCHAR(50), -- e.g., '10', 'Form 4', 'Year 10'
    MainTeacherID INT, -- Primary teacher responsible for this class
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (SchoolID) REFERENCES Schools(SchoolID) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (MainTeacherID) REFERENCES Teachers(TeacherID) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX (SchoolID, ClassName)
);

-- Students Table: Details of enrolled students.
-- Linked to the Users table for login (optional for younger students).
CREATE TABLE IF NOT EXISTS Students (
    StudentID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT UNIQUE, -- Foreign Key to Users table for student login (nullable if not all students log in)
    SchoolID INT NOT NULL,
    ClassID INT NOT NULL, -- The class the student belongs to
    FirstName VARCHAR(255) NOT NULL,
    LastName VARCHAR(255) NOT NULL,
    DateOfBirth DATE,
    Gender VARCHAR(10), -- 'Male', 'Female', 'Other'
    AdmissionDate DATE DEFAULT (CURRENT_DATE),
    StudentEmail VARCHAR(255) UNIQUE, -- Student's personal email (if different from User.Email or no login)
    ContactNumber VARCHAR(20), -- Student's direct contact (if applicable)
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE SET NULL ON UPDATE CASCADE, -- SET NULL if user account is deleted but student record remains
    FOREIGN KEY (SchoolID) REFERENCES Schools(SchoolID) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (ClassID) REFERENCES Classes(ClassID) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX (SchoolID, ClassID),
    INDEX (LastName, FirstName)
);

-- Parents Table: Profile details for parents/guardians.
-- Linked to the Users table for authentication.
CREATE TABLE IF NOT EXISTS Parents (
    ParentID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT UNIQUE NOT NULL, -- Foreign Key to Users table for login
    SchoolID INT NOT NULL, -- To link parents to a specific school if needed
    FirstName VARCHAR(255) NOT NULL,
    LastName VARCHAR(255) NOT NULL,
    ContactNumber VARCHAR(20), -- Parent's direct contact
    RelationshipToStudent VARCHAR(50), -- e.g., 'Mother', 'Father', 'Guardian'
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (SchoolID) REFERENCES Schools(SchoolID) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- StudentParents Junction Table: To handle many-to-many relationship (a student can have multiple parents, a parent can have multiple students).
CREATE TABLE IF NOT EXISTS StudentParents (
    StudentID INT NOT NULL,
    ParentID INT NOT NULL,
    PRIMARY KEY (StudentID, ParentID), -- Composite Primary Key
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (ParentID) REFERENCES Parents(ParentID) ON DELETE CASCADE ON UPDATE CASCADE
);


-- Marks Table: Records individual assessment marks for students in courses.
CREATE TABLE IF NOT EXISTS Marks (
    MarkID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT NOT NULL,
    CourseID INT NOT NULL,
    TeacherID INT, -- The teacher who recorded the mark (can be NULL if marks are imported)
    MarkType VARCHAR(50) NOT NULL, -- e.g., 'Test', 'Exam', 'CAT', 'Practical', 'Theory'
    Score DECIMAL(5,2) NOT NULL, -- The actual mark obtained (e.g., 85.50)
    MaxScore DECIMAL(5,2) DEFAULT 100.00, -- Maximum possible score for this assessment
    DateRecorded DATE NOT NULL,
    Notes TEXT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (CourseID) REFERENCES Courses(CourseID) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (TeacherID) REFERENCES Teachers(TeacherID) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX (StudentID, CourseID, MarkType),
    INDEX (DateRecorded)
);

-- Attendance Table: Tracks student attendance for specific classes/lessons.
CREATE TABLE IF NOT EXISTS Attendance (
    AttendanceID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT NOT NULL,
    ClassID INT NOT NULL,
    CourseID INT, -- Optional: If attendance is tracked per course within a class
    Date DATE NOT NULL,
    IsPresent BOOLEAN NOT NULL DEFAULT TRUE, -- TRUE for present, FALSE for absent
    ReasonForAbsence TEXT, -- Optional field for absence reason (e.g., 'Sick', 'Approved Leave')
    RecordedByUserID INT, -- UserID from the Users table (Teacher or Admin)
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (ClassID) REFERENCES Classes(ClassID) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (CourseID) REFERENCES Courses(CourseID) ON DELETE SET NULL ON UPDATE CASCADE, -- Can be NULL if attendance is class-wide
    FOREIGN KEY (RecordedByUserID) REFERENCES Users(UserID) ON DELETE SET NULL ON UPDATE CASCADE,
    UNIQUE (StudentID, ClassID, Date, CourseID), -- Ensure unique attendance entry per student per class/course per day
    INDEX (Date, ClassID)
);

-- Discipline Table: Records student disciplinary incidents.
CREATE TABLE IF NOT EXISTS Discipline (
    DisciplineID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT NOT NULL,
    IncidentDate DATE NOT NULL,
    IncidentDescription TEXT NOT NULL,
    ActionTaken TEXT, -- e.g., 'Warning', 'Suspension', 'Detention'
    ReportedByUserID INT, -- UserID from Users table (Teacher or Admin)
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (ReportedByUserID) REFERENCES Users(UserID) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX (StudentID, IncidentDate)
);

-- Conferences Table: Details of scheduled online meetings.
CREATE TABLE IF NOT EXISTS Conferences (
    ConferenceID INT AUTO_INCREMENT PRIMARY KEY,
    SchoolID INT NOT NULL,
    Title VARCHAR(255) NOT NULL,
    Description TEXT,
    ScheduledStartTime DATETIME NOT NULL,
    ScheduledEndTime DATETIME NOT NULL,
    MeetingLink VARCHAR(500) UNIQUE NOT NULL, -- Unique link for the meeting (e.g., Zoom, Google Meet link)
    HostUserID INT NOT NULL, -- UserID from Users table (Teacher or Admin)
    Status VARCHAR(50) DEFAULT 'Scheduled', -- 'Scheduled', 'InProgress', 'Completed', 'Cancelled'
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (SchoolID) REFERENCES Schools(SchoolID) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (HostUserID) REFERENCES Users(UserID) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX (SchoolID, ScheduledStartTime)
);

-- ConferenceParticipants Table: Many-to-many relationship for who attends which conference.
CREATE TABLE IF NOT EXISTS ConferenceParticipants (
    ConferenceID INT NOT NULL,
    UserID INT NOT NULL, -- UserID from Users table (Student, Teacher, Parent, Admin)
    PRIMARY KEY (ConferenceID, UserID), -- Composite Primary Key
    JoinedAt DATETIME,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ConferenceID) REFERENCES Conferences(ConferenceID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Notifications Table: Generic table for various types of alerts/messages.
CREATE TABLE IF NOT EXISTS Notifications (
    NotificationID INT AUTO_INCREMENT PRIMARY KEY,
    RecipientUserID INT NOT NULL, -- UserID from Users table
    NotificationType VARCHAR(100) NOT NULL, -- e.g., 'NewMark', 'AttendanceAlert', 'MeetingReminder', 'GeneralAnnouncement'
    Message TEXT NOT NULL,
    IsRead BOOLEAN DEFAULT FALSE,
    LinkURL VARCHAR(500), -- Optional: Link to relevant page/resource
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (RecipientUserID) REFERENCES Users(UserID) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX (RecipientUserID, IsRead, CreatedAt)
);

-- Reports Table: Stores generated reports or references to them.
CREATE TABLE IF NOT EXISTS Reports (
    ReportID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT, -- Null for general school reports, otherwise linked to a specific student
    ReportType VARCHAR(100) NOT NULL, -- e.g., 'Report Card', 'Attendance Summary', 'Discipline Summary'
    ReportDate DATE NOT NULL,
    GeneratedByUserID INT, -- UserID from Users table who generated the report
    ReportContent TEXT, -- Storing concise report content (e.g., JSON summary, or direct text)
    ReportFilePath VARCHAR(500), -- Path to an external file (e.g., PDF) if reports are stored as files
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (GeneratedByUserID) REFERENCES Users(UserID) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX (ReportType, ReportDate)
);

-- AI_StudentPerformanceInsights Table: Stores AI-generated insights for student performance.
CREATE TABLE IF NOT EXISTS AI_StudentPerformanceInsights (
    InsightID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT NOT NULL,
    InsightDate DATE NOT NULL,
    PerformanceScore DECIMAL(5,2), -- e.g., overall GPA or calculated performance metric
    Strengths TEXT,
    Weaknesses TEXT,
    Recommendations TEXT,
    PredictedPerformance TEXT, -- AI's prediction
    GeneratedByAI BOOLEAN DEFAULT TRUE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX (StudentID, InsightDate)
);

-- AI_ConferenceSummaries Table: Stores AI-generated summaries and action items from conferences.
CREATE TABLE IF NOT EXISTS AI_ConferenceSummaries (
    SummaryID INT AUTO_INCREMENT PRIMARY KEY,
    ConferenceID INT NOT NULL,
    SummaryText TEXT NOT NULL,
    KeyDecisions TEXT,
    ActionItems TEXT,
    GeneratedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ConferenceID) REFERENCES Conferences(ConferenceID) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX (ConferenceID)
);

-- End of Script