ALTER TABLE hms_student_profiles add constraint hms_student_profile_user UNIQUE (user_id);

ALTER TABLE hms_deadlines ADD COLUMN edit_profile_begin_timestamp integer;
ALTER TABLE hms_deadlines ADD COLUMN edit_profile_end_timestamp integer;

UPDATE hms_deadlines SET edit_profile_begin_timestamp = 1177348714;
UPDATE hms_deadlines SET edit_profile_end_timestamp = 1177349774;

ALTER TABLE hms_deadlines ALTER COLUMN edit_profile_begin_timestamp SET NOT NULL;
ALTER TABLE hms_deadlines ALTER COLUMN edit_profile_end_timestamp SET NOT NULL;

ALTER TABLE hms_student_profiles ADD COLUMN alternate_email character varying(64);
ALTER TABLE hms_student_profiles ADD COLUMN aim_sn character varying(32);
ALTER TABLE hms_student_profiles ADD COLUMN yahoo_sn character varying(32);
ALTER TABLE hms_student_profiles ADD COLUMN msn_sn character varying(32);

ALTER TABLE hms_learning_community_assignment ADD COLUMN gender character varying(32);
ALTER TABLE hms_learning_community_assignment ALTER COLUMN gender SET NOT NULL;

ALTER TABLE hms_learning_community_assignment DROP COLUMN assigned_by_user;
ALTER TABLE hms_learning_community_assignment DROP COLUMN assigned_by_initials;

ALTER TABLE hms_learning_community_assignment ADD COLUMN assigned_by character varying(32);
UPDATE hms_learning_community_assignment SET assigned_by = 'unknown';
ALTER TABLE hms_learning_community_assignment ALTER COLUMN assigned_by SET NOT NULL;

CREATE TABLE hms_roommate_approval (
    id INTEGER NOT NULL,
    number_roommates SMALLINT NOT NULL,
    roommate_zero CHARACTER VARYING(32) NOT NULL,
    roommate_zero_approved SMALLINT NOT NULL,
    roommate_zero_personal_hash CHARACTER VARYING(32) NOT NULL,
    roommate_one CHARACTER VARYING(32) NOT NULL,
    roommate_one_approved SMALLINT NOT NULL,
    roommate_one_personal_hash CHARACTER VARYING(32) NOT NULL,
    roommate_two CHARACTER VARYING(32),
    roommate_two_approved SMALLINT,
    roommate_two_personal_hash CHARACTER VARYING(32),
    roommate_three CHARACTER VARYING(32),
    roommate_three_approved SMALLINT,
    roommate_three_personal_hash CHARACTER VARYING(32),
    PRIMARY KEY (id)
);

