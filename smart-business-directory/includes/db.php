<?php
// Database helper functions

function getOne($table, $id, $id_field = 'id') {
    global $conn;
    $id = (int)$id;
    $query = "SELECT * FROM $table WHERE $id_field = $id";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function getAll($table, $order_by = 'id DESC') {
    global $conn;
    $query = "SELECT * FROM $table ORDER BY $order_by";
    $result = mysqli_query($conn, $query);
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    return $items;
}

function insert($table, $data) {
    global $conn;
    $fields = array_keys($data);
    $values = array_map(function($v) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $v) . "'";
    }, array_values($data));
    
    $query = "INSERT INTO $table (" . implode(',', $fields) . ") VALUES (" . implode(',', $values) . ")";
    if (mysqli_query($conn, $query)) {
        return mysqli_insert_id($conn);
    }
    return false;
}

function update($table, $data, $where, $where_value) {
    global $conn;
    $sets = [];
    foreach ($data as $key => $value) {
        $sets[] = "$key = '" . mysqli_real_escape_string($conn, $value) . "'";
    }
    $query = "UPDATE $table SET " . implode(',', $sets) . " WHERE $where = '$where_value'";
    return mysqli_query($conn, $query);
}

function delete($table, $where, $value) {
    global $conn;
    $query = "DELETE FROM $table WHERE $where = '$value'";
    return mysqli_query($conn, $query);
}

function countRows($table, $where = '') {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM $table";
    if ($where) {
        $query .= " WHERE $where";
    }
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}
?>