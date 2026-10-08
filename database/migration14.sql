-- Migration 14: Job application deadlines now support date AND time, and
-- legacy content that was stored with HTML entities is cleaned up so that
-- apostrophes (') and ampersands (&) display correctly on the site.

-- 1) Allow datetime values in the job application deadline column
ALTER TABLE jobs MODIFY application_deadline DATETIME DEFAULT NULL;

-- 2) Decode one layer of previously double-encoded HTML entities in the
--    plain-text columns of the jobs table so they display correctly.
UPDATE jobs SET
  company_name = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(company_name,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>'),
  title = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(title,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>'),
  province = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(province,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>'),
  district = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(district,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>'),
  location = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(location,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>'),
  experience_level = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(experience_level,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>'),
  education_level = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(education_level,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>'),
  application_email = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(application_email,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>')
WHERE company_name LIKE '%&%' OR title LIKE '%&%' OR province LIKE '%&%' OR district LIKE '%&%'
   OR location LIKE '%&%' OR experience_level LIKE '%&%' OR education_level LIKE '%&%'
   OR application_email LIKE '%&%';

-- 3) Clean up entities in scholarship and company tables as well.
UPDATE scholarships SET
  title = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(title,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>'),
  description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(description,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>')
WHERE title LIKE '%&%' OR description LIKE '%&%';

UPDATE scholarship_listings SET
  title = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(title,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>'),
  organization = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(organization,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>'),
  description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(description,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>'),
  eligibility = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(eligibility,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>')
WHERE title LIKE '%&%' OR organization LIKE '%&%' OR description LIKE '%&%' OR eligibility LIKE '%&%';

UPDATE companies SET
  name = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(name,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>'),
  description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(description,
    '&amp;#039;', "'"), '&amp;quot;', '"'), '&amp;', '&'), '&#039;', "'"), '&quot;', '"'), '&lt;', '<'), '&gt;', '>')
WHERE name LIKE '%&%' OR description LIKE '%&%';