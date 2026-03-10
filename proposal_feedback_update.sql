-- Add feedback column for professor comments on proposals
ALTER TABLE Proposals ADD COLUMN prof_feedback TEXT AFTER status;