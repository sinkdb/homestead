alter table hms_student_profiles add column banner_id integer;
alter table hms_student_profiles add column about_me character varying;

alter table hms_student_profiles drop column aim_sn;
alter table hms_student_profiles drop column yahoo_sn;
alter table hms_student_profiles drop column msn_sn;

alter table hms_student_profiles add column fb_link character varying;
alter table hms_student_profiles add column instagram_sn character varying;
alter table hms_student_profiles add column twitter_sn character varying;
alter table hms_student_profiles add column tumblr_sn character varying;
alter table hms_student_profiles add column kik_sn character varying;
