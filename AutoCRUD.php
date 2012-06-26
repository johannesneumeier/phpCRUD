<?php
/*
 * TODO:
 * - image upload + safe filename to field
 * - localisation for date/time strings, possibliy via custom function for field
 * - javascript goodies: form validation, ajax upload
 * - localisation for all strings to seperate file
 * - errors
 * - options to options array throughout, otherwise setting of class variables allowed
 * - data validation based on field type and length
 * - TIME mysql field type
 * 
 */
 
class AutoCRUD {
    var $table;
    
    // if all AutoCRUD instances use the same database connection info, this can be
    // set here instead of passing it with each instance creation as options
    var $dbhost = 'localhost';
    var $dbuser = 'autocrud';
    var $dbname = 'autocrud';
    var $dbpass = '4716';
    
    var $templates_dir = 'templates/'; // default location for form templates
    var $styles_dir    = 'style/';
    var $lib_dir       = 'libraries/';
    var $rows_per_page = 10;           // default number of rows in listing
    var $convertFieldNames = TRUE;     // default for converting col names to readable names
    
    var $fields       = array(); // internal list of all fields
    var $primaryField = '';      // internal helper to get primary key field TODO composite primary key
    
    var $hidden       = array(); // cols listed in this array are not shown at all
    var $disabled     = array(); // cols listed in this array are not editable
    var $listing      = array(); // cols listed in this array get shown in listing
    var $upload       = array();
    var $convert      = array();
    
    // pagination helpers
    var $orderBy;
    var $orderDir;
    var $offset;
    var $limit;
    
    var $dateFormat = "d.m.Y";
    var $dateFormatDatePicker = "dd.mm.yy";
    var $timeFormat = "H:i:s";
    
    function AutoCRUD($_table, $options = NULL) {
        $this->table = $_table;
        
        if ($options !== NULL && is_array($options)) {
            foreach ($options as $option => $value) {
                $this->$option = $value;
            }
        }        
        $this->connect();
        $this->getFields();
        
        print_r($this->fields);
        
        $action   = isset($_GET['action'])    ? $_GET['action']    : NULL;
        $id       = isset($_GET['primary'])   ? $_GET['primary']   : NULL;
        $submit   = isset($_POST['send'])     ? TRUE               : FALSE;
        
        $this->orderBy  = isset($_GET['order-by'])  ? $_GET['order-by']  : $this->primaryField;
        $this->orderDir = isset($_GET['order-dir']) ? $_GET['order-dir'] : 'DESC';

        $this->perPage  = isset($_GET['per_page'])  ? $_GET['per_page']  : $this->rows_per_page;
        $this->page     = isset($_GET['page'])      ? $_GET['page']      : 1;
        $this->offset   = ($this->page - 1) * $this->perPage;
        $this->limit    = $this->offset + $this->perPage;
        
        foreach($_FILES as $input => $file) {
            $input = substr($input, 0, -5);
            if (in_array($input, array_keys($this->upload))) {
                $this->upload($input, $file);
            }
        } 
        
        echo '<div id="autocrud">';
        require_once($this->templates_dir . 'header.php');        
        
        if (isset($action)) {
            if (@!method_exists($this, $action)) {
                exit('Action not defined');
            }
            $this->$action($id, $submit);
        } else {
            $this->listing();
            $this->pagination();
        }
        echo '</div>';
    }
    
    function connect() {
        $connection = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass);
        mysql_select_db($this->dbname, $connection) or die(mysql_error());
    }
    
    function getFields() {
        $sql = "SHOW COLUMNS FROM $this->table";
        $res = mysql_query($sql) or die(mysql_error());
        while ($row = mysql_fetch_assoc($res)) {
            $this->fields[$row['Field']] = $row;
            if ($row['Key'] === 'PRI') {
                $this->primaryField = $row['Field'];
            }
        }
    }
    
    function getData($id = NULL) {
        return $id !== NULL ? $this->getDataRows($id) : $this->getDataRows();
    }
        
    function getDataRows($id = NULL) {
        $sql = "SELECT * FROM $this->table";
        if ($id !== NULL) {
            $sql .= " WHERE $this->primaryField = $id";
        } else {
            if (!isset($this->orderBy)) {
                $sql .= " ORDER BY $this->primaryField DESC ";
            } else {
                $sql .= " ORDER BY $this->orderBy $this->orderDir ";
            }
            
            $sql .= " LIMIT $this->offset, $this->limit ";
        }
        $res = mysql_query($sql) or die(mysql_error());
        $rows = FALSE;
        while ($row = mysql_fetch_assoc($res)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    function pagination() {
        $sql = "SELECT COUNT(*) FROM $this->table";
        $res = mysql_query($sql);
        $count = mysql_fetch_array($res);        
        $rows = $count[0];        
        $pagesTotal = ceil($rows / $this->perPage);
        if ($pagesTotal > 1) {
            echo '<ul class="autocrud-pagination">';
            for ($i = 1; $i <= $pagesTotal; $i++) {
                if ($i != $this->page) {
                    echo '<li class="autocrud-listing-page">
                                <a href="?order-by=' . $this->orderBy .
                                '&order-dir=' . $this->orderDir .
                                '&page=' . $i . 
                                '&per-page=' . $this->perPage .
                                '">' . $i . '</a>
                            </li>';
                } else {
                    echo '<li class="autocrud-listing-page autocrud-listing-page-current">
                            ' . $i . '
                          </li>';
                }
            }        
            echo '<ul>';
        }
    }
    
    function insertDataRow() {
        $insert = array();
        foreach ($this->fields as $field) {
            if ($field['Field'] !== $this->primaryField && in_array($field['Field'], array_keys($_POST))) {
                $insert[$field['Field']] = mysql_real_escape_string($_POST[$field['Field']]);
            }
        }
        
        if (!empty($insert)) {
            $sql = "INSERT INTO $this->table ( ";
            foreach ($insert as $field => $value) {
                $sql .= "$field, ";
            }
            $sql = substr($sql, 0, -2) . " ) VALUES ( ";
            foreach ($insert as $field => $value) {
                $sql .= "'$value', ";
            }
            $sql = substr($sql, 0, -2) . ")";
            mysql_query($sql) or die(mysql_error());
            return mysql_insert_id();
        }
        return FALSE;
    }
    
    function updateDataRow($id = NULL) {
        $sql = "UPDATE $this->table SET ";
        foreach ($this->fields as $field) {
            if ($field['Field'] !== $this->primaryField && in_array($field['Field'], array_keys($_POST))) {
                echo '<br/>update field: ' . $field['Field'];
                $sql .= $field['Field'] . " = '" . mysql_real_escape_string($_POST[$field['Field']]) . "', ";
            }
        }
        $sql = substr($sql, 0, -2);
        $sql .= "WHERE $this->primaryField = '$id' LIMIT 1";
        mysql_query($sql) or die(mysql_error());
    }
    
    function deleteDataRow($id = NULL) {
        $sql = "DELETE FROM $this->table WHERE $this->primaryField = '$id' LIMIT 1";
        mysql_query($sql) or die(mysql_error());
    }
    
    function listing() {
        require_once($this->templates_dir . 'listing.php');
    }
    
    function create($ignore = FALSE, $submit = FALSE) {
        if ($submit) {
            if ($insert_id = $this->insertDataRow()) {
                $this->edit($insert_id);
            }
        } else {
            echo '<form method="post">';
            echo '<input type="hidden" name="send" value="1" />';
            $data = NULL;
            require_once($this->templates_dir . 'edit.php');
            echo '<button>Create</button>';
            echo '</form>';
        }
    }
    
    function edit($id, $submit = FALSE) {
        if ($submit) {
            $this->updateDataRow($id);
        }
        
        $data = $this->getData($id);
        $data = $data[0];
        
        echo '<form method="post" class="autocrud-form" enctype="multipart/form-data">';
        echo '<input type="hidden" name="send" value="1" />';
        require_once($this->templates_dir . 'edit.php');
        echo '<button>Update</button>';
        echo '</from>';
    }

    function delete($id, $submit = FALSE) {        
        if ($submit) {
            $this->deleteDataRow($id);
            $this->listing();
        } else {
        
            $data = $this->getData($id);
            $data = $data[0];
            echo '<form method="post">';
            echo '<input type="hidden" name="send" value="1" />';
            require_once($this->templates_dir . 'delete.php');
            echo '<button>Delete</button>';
            echo '</form>';
        }
    }

    function upload($name, $file) {
        echo "<br/>upload($name)";
        if ($file['size'] > $this->upload[$name]['max_size']) {
            echo 'file to large';
        }
         
        if (move_uploaded_file($file['tmp_name'], $this->upload[$name]['target'] . $file['name'])) {
            $_POST[$name] = $file['name'];
        }        
    }
    
    function displayFieldName($fieldName) {
        return ucfirst(strtolower(preg_replace('/([^a-zA-Z0-9])/i', ' ', $fieldName)));
    }
    
    function displayFieldData($fieldName, $fieldData) {
        $fieldType = $this->fields[$fieldName]['Type'];
        if (in_array($fieldName, array_keys($this->convert)) && function_exists($this->convert[$fieldName])) {
            return $this->convert[$fieldName]($fieldData);
        } else {
            if ($fieldType == 'timestamp') {
                return empty($fieldData) || $fieldData == '0000-00-00 00:00:00' 
                       ? $fieldData 
                       : date("$this->dateFormat $this->timeFormat", strtotime($fieldData));
            } else if ($fieldType == 'date') {
                return empty($fieldData) || $fieldData == '0000-00-00'
                       ? $fieldData
                       : date("$this->dateFormat", strtotime($fieldData));
            } else {
                return $fieldData;
            }
        }
    }
    
    function visibleListingField($fieldName) {        
        if (!in_array($fieldName, $this->hidden) &&
            (empty($this->listing) || !empty($this->listing) && in_array($fieldName, $this->listing))
        ) {
            return true;
        } else {
            return false;
        }
    }
    
    function localizeDateTime($fieldType, $datetime) {
        return empty($string) || $string == '0000-00-00 00:00:00' ? $string : date("d.m.Y H:i:s", strtotime($string));
    }
}

?>