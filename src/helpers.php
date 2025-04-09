<?php

const DB_HOST = 'localhost';
const DB_NAME = 'pr0704';
const DB_USER = 'root';
const DB_PASS = 'YES';

function getDB(): bool|mysqli {
    return mysqli_connect(DB_HOST, DB_USER, '', DB_NAME);
}
?>