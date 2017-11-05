# repository_s3bucket 

![built status](https://travis-ci.org/ewallah/moodle-repository_s3bucket.svg?branch=master)

Instead of giving all users access to your complete S3 account, this plugin makes it
possible to give teachers and managers access to a specific S3 folder (bucket).

Multiple instances are supported, you only have to create a IAM user who has read access
to your S3 root folder, but read and write permissions to your S3 bucket.

TODO:
1. implement cache.
    