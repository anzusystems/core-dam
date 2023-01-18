-- blog sys api user
UPDATE user
SET api_token = '$argon2id$v=19$m=65536,t=2,p=2$WjQ0U3Q5eFd2cXZnSDh0SQ$+BfXHx21N6KmQ0tbazTAsPqCmkfvhybZCq6N9AFAEUw' --TOKEN_sys_anzu_blog
WHERE sso_id = 1830632;
