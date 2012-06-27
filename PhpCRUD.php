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
 * - duplicate row
 */
 
class PhpCRUD {
    
    var $settings = array();
    
    var $defaults = array(     
        'dbhost' => 'localhost',
        'dbuser' => '',
        'dbname' => '',
        'dbpass' => '',
        'table'  => '',
        
        'baseDir' => '',
        'allowedActions' => array('create', 'edit', /*'copy', */'delete'),
        'jquery-ui-js' => '',
        'jquery-ui-css' => ''
    );
    
    
    var $includes_dir  = 'includes/'; // default location for form templates
    var $styles_dir    = 'style/';
    var $lib_dir       = 'libraries/';
    var $jquery_ui_js  = '';
    var $jquery_ui_css = '';
    
    var $rows_per_page = 10;           // default number of rows in listing
    var $convertFieldNames = TRUE;     // default for converting col names to readable names
    
    var $fields       = array(); // internal list of all fields
    var $primaryField = '';      // internal helper to get primary key field TODO composite primary key
    
    var $options = array(
        'hidden'   => array(),
        'disabled' => array(),
        'listing'  => array(),
        'upload'   => array(),
        'select'   => array(),
        'radio'    => array(),
        'convert'  => array(),
        'description' => array()        
    );
    
    var $hidden       = array(); // cols listed in this array are not shown at all
    var $disabled     = array(); // cols listed in this array are not editable
    var $listing      = array(); // cols listed in this array get shown in listing
    var $upload       = array();
    var $convert      = array();
    var $select       = array();
    var $radio        = array();
    
    // pagination helpers
    var $orderBy;
    var $orderDir;
    var $offset;
    var $limit;
    
    var $dateFormat = "d.m.Y";
    var $dateFormatDatePicker = "dd.mm.yy";
    var $timeFormat = "H:i:s";
    
    //function AutoCRUD($_table, $options = NULL) {
    function AutoCRUD($_table = NULL, $options = NULL) {
        $settings['dbhost'] = !empty($defaults['dbhost']) ? $defaults['dbhost'] : NULL;
        $settings['dbuser'] = !empty($defaults['dbuser']) ? $defaults['dbuser'] : NULL;
        $settings['dbname'] = !empty($defaults['dbname']) ? $defaults['dbname'] : NULL;
        $settings['dbpass'] = !empty($defaults['dbpass']) ? $defaults['dbpass'] : NULL;
    }
    
    function make() {
        //echo "---make() ";
        
        // fill settings with defaults or custom settings where given
        $this->settings = array_merge($this->defaults, $this->settings);
        
        $this->jquery_ui_js = empty($this->settings['jquery-ui-js']) 
                            ? $this->baseDir . $this->lib_dir . 'jquery-ui-1.8.21.custom/js/jquery-ui-1.8.21.custom.min.js' 
                            : $this->settings['jquery-ui-js'];
                            
        $this->jquery_ui_js = empty($this->settings['jquery-ui-css']) 
                            ? $this->baseDir . $this->lib_dir . 'jquery-ui-1.8.21.custom/css/ui-lightness/jquery-ui-1.8.21.custom.css' 
                            : $this->settings['jquery-ui-css'];
        
        echo '<pre>';        
        //print_r($this->settings);
        //print_r($this->options);
        $this->connect();
        $this->getFields();        
        //print_r($this->fields);
        echo '</pre>';
        
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
            echo "---trying to upload $input";
            $input = substr($input, 0, -5);
            if (in_array($input, array_keys($this->options['upload']))) {
                var_dump($this->upload($input, $file));
            }
        } 
        
        $this->printHead();
        
        echo '<div id="autocrud">';
        
        $this->printHeader();
        
        if (isset($action)) {
            if (@!method_exists($this, $action)) {
                exit('Action not defined');
            }
            if (!in_array($action, $this->settings['allowedActions'])) {
                exit('Action not allowed');
            }
            $this->$action($id, $submit);
        } else {
            $this->listing();
            $this->pagination();
        }
        echo '</div>';
    }
    
    function connect() {
        //echo "connect()" . $this->settings['dbname'];
        $connection = mysql_connect($this->settings['dbhost'], $this->settings['dbuser'], $this->settings['dbpass']) or die(mysql_error());
        mysql_select_db($this->settings['dbname'], $connection) or die(mysql_error());
    }
    
    function setup($option = NULL, $setting = NULL) {
        //echo "---setup($option, $setting) ";
        if (in_array($option, array_keys($this->defaults)) && $setting != NULL) {
            $this->settings[$option] = $setting;
        }
    }
    
    function setupField($type = NULL, $field = NULL, $setting = NULL) {
        //echo "---setupField($type, $field, $setting)";
        if (in_array($type, array_keys($this->options))) {
            if ($setting !== NULL && is_array($setting)) {
                $this->options[$type][$field] = $setting;
            } else if ($setting !== NULL && !is_array($setting)) {
                $this->options[$type][$field] = $setting;
            } else {
                $this->options[$type][] = $field;
            }
        }
    }
    
    function getFields() {
        $sql = "SHOW COLUMNS FROM " . $this->settings['table'] . " ";
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
        $sql = "SELECT * FROM " . $this->settings['table'] . " ";
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
        $sql = "SELECT COUNT(*) FROM " . $this->settings['table'] . " ";
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
            $sql = "INSERT INTO " . $this->settings['table'] . "  ( ";
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
        $sql = "UPDATE " . $this->settings['table'] . "  SET ";
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
        $sql = "DELETE FROM " . $this->settings['table'] . "  WHERE $this->primaryField = '$id' LIMIT 1";
        mysql_query($sql) or die(mysql_error());
    }
    
    function listing() {
        
        require_once($this->includes_dir . 'listing.php');
    }
    
    function create($ignore = FALSE, $submit = FALSE) {
        if ($submit) {
            if ($insert_id = $this->insertDataRow()) {
                $this->edit($insert_id);
            }
        } else {
            echo '<form method="post" class="autocrud-form">';
            echo '<input type="hidden" name="send" value="1" />';
            $data = NULL;
            require_once($this->includes_dir . 'edit.php');
            echo '<button class="autocrud-button autocrud-button-create">Create</button>';
            echo '</form>';
            echo '<form method="get"><button class="autocrud-button autocrud-button-cancel">Cancel</button></form>';
        }
    }
    
    function edit($id, $submit = FALSE) {
        //echo "edit($id, $submit)";
        if ($submit) {
            $this->updateDataRow($id);
        }
        
        $data = $this->getData($id);
        $data = $data[0];
        
        echo '<form method="post" class="autocrud-form" enctype="multipart/form-data">';
        echo '<input type="hidden" name="send" value="1" />';
        require_once($this->settings['baseDir'] . $this->includes_dir . 'edit.php');
        echo '<button class="autocrud-button autocrud-button-update">Update</button>';
        echo '</form>';
        echo '<form method="get"><button class="autocrud-button autocrud-button-cancel">Cancel</button></form>';
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
            require_once($this->settings['baseDir'] . $this->includes_dir . 'delete.php');
            echo '<button class="autocrud-button autocrud-button-update">Delete</button>';
            echo '</form>';            
            echo '<form method="get"><button class="autocrud-button autocrud-button-cancel">Cancel</button></form>';
        }
    }

    function upload($name, $file) {
        echo "<br/>upload($name)";
        if ($file['size'] > $this->options['upload'][$name]['max_size']) {
            echo 'file to large';
        }
        
        if (!is_dir($this->options['upload'][$name]['target'])) {
            exit('no such dir ' . $this->options['upload'][$name]['target']);
        }
        
        if ($error = move_uploaded_file($file['tmp_name'], $this->options['upload'][$name]['target'] . $file['name'])) {
            $_POST[$name] = $file['name'];
        } else {
            exit('failed to upload: '.$error);
        }
        return TRUE;
    }
    
    function displayFieldName($fieldName) {
        return ucfirst(strtolower(preg_replace('/([^a-zA-Z0-9])/i', ' ', $fieldName)));
    }
    
    function displayFieldData($fieldName, $fieldData) {
        $fieldType = $this->fields[$fieldName]['Type'];
        if (in_array($fieldName, array_keys($this->options['radio']))) {
            if (in_array($fieldData, array_keys($this->options['radio'][$fieldName]))) {
                return $this->options['radio'][$fieldName][$fieldData];
            } else {
                return 'invalid value supplied for radio';
            }
        } else if (in_array($fieldName, array_keys($this->convert)) && function_exists($this->convert[$fieldName])) {
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
        if (!in_array($fieldName, $this->options['hidden']) &&
            (empty($this->options['listing']) || !empty($this->options['listing']) && 
             in_array($fieldName, $this->options['listing']))
        ) {
            return true;
        } else {
            return false;
        }
    }
    
    function localizeDateTime($fieldType, $datetime) {
        return empty($string) || $string == '0000-00-00 00:00:00' ? $string : date("d.m.Y H:i:s", strtotime($string));
    }
    
    function fieldOption($fieldName = NULL, $fieldOption) {
        if ($fieldName !== NULL &&
            $fieldOption !== NULL &&
            in_array($fieldName, array_keys($this->fields)) &&
            in_array($fieldOption, array_keys($this->options)) &&
            in_array($fieldName, array_keys($this->options[$fieldOption]))
        ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    function printHeader() {
        echo '        
        <div class="autocrud-header">
            <form method="get">
                <input type="hidden" name="action" value="create" />
                <button class="autocrud-button autocrud-button-create">Create new</button>
            </form>
            <form method="get">
                <button class="autocrud-button autocrud-button-view-all">View all</button>
            </form>
        </div>';
    }
    
    function printHead() {
        require_once($this->settings['baseDir'] . $this->includes_dir . 'header.php');        
    }
}

?>