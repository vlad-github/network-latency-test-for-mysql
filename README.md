**usage**: mysql_latency_benchmark.php <db_host> <db_port> <db_user> <db_pass> [number of queries]

# network-latency-test-for-mysql
ubuntu howto

on the database host
```
mysql
mysql> CREATE DATABASE test;
mysql> CREATE USER 'test'@'%' IDENTIFIED BY 'test' WITH MAX_USER_CONNECTIONS 10;
mysql> GRANT ALL PRIVILEGES ON *.* TO 'test'@'%';
```

on the client
```
apt install -y php-cli php-mysql
screen -S test
php -q mysql_latency_benchmark.php <remote_host> 3306 test test 
```
