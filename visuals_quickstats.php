<?php
    /**
     * 
     * Visuals_quickstats class draws items for the quickstats section
     * 
     */

    class visuals_quickstats extends visuals {

        private $array_item = array();
        private $array_subject = array();
        private $array_type = array();
        private $array_varsta = array();
        private $itemfound = 0;
        private $notfound = NULL;
        private $found = 0;
        private $calculation = NULL;
        private $operators = NULL;

        /**
         *
         * Draw form for editting the quickstat modules
         * Loop through all module types, needed to order all the modules. Apply custom color from the type
         * Loop through all of the modules in the current type in the loop
         * Check if the current module in the loop is active (saved in the database) and draw the button
         *
         */

        function vi_edit_quickstat_modules() {

            // Display title
            echo $this->vc_title('Quickstats modules');

            // Start form
            echo '<form method="post" action="index.php?page=1&sub=9">';

                // Loop through all modules types
                foreach($this->qu_all_module_types() as $type_key => $type_value) {
                    echo $this->vc_small_title($type_value['name'], $type_value['rgb_color']);

                    // Set custom style per module type
                    // Border color per module (type)
                    echo '<style>';
                        echo '.label_'.$type_value['id'].' {';
                            echo 'border: 1px solid '.$type_value['rgb_color'].';';
                        echo '}';
                    echo '</style>';

                    // Create selections div to contain all buttons
                    echo '<div class="selections">';

                        // Loop through all modules in current type in loop
                        foreach($this->qu_get_modules_from_type($type_value['id']) as $module_key => $module_value) {
                            
                            // Set checked
                            $checked = NULL;
                            if ($this->qu_is_quickstat_module($module_value['id']) > 0) {
                                $checked = ' checked '; 
                            }
                            
                            // Display input buttons
                            // The checkbox itself will be hidden, the label acts like the button
                            echo '<input type="checkbox" name="'.$module_value['id'].'" id="'.$module_value['name'].'" '.$checked.' />';
                            echo '<label for="'.$module_value['name'].'" class="label_'.$type_value['id'].'">'.$module_value['name'].'</label>';
                        }
                    echo '</div>';
                }

                // Add form end
                echo $this->vc_add_end('quickstats_modules');
            echo '</form>';
        }

        /**
         * 
         * Display all quickstats calculations
         * If no calculations are found, display "No calculations"
         * 
         */
        
        function vi_all_quickstat_calculations() {

            // Display title
            echo $this->vc_title('All quickstats calculations');

            // Check if there are calculations in the database
            if ($this->qu_count_quickstats_calculations() > 0) {

                // Display calculations
                foreach($this->qu_quickstats_calculations() as $calc_key => $calc_value) {
                    echo $this->vc_table_line(
                        '<div class="width300 tablecell">'.$calc_value['name'].'</div>'.
                        '<div class="width100 tablecell">Edit</div>'.
                        '<div class="width100 tablecell">Delete</div>'
                    );
                }
            } else {
                echo 'No calculations';
            }

            // Display add button
            echo $this->vc_add_button('Add calculation', 'index.php?page=1&sub=9&action=3');
        }

        /**
         * 
         * Draw form to add quickstat calculations
         * Form is also used when, after reviewing the calculation, the calculation needs editting (stored in $_SESSION) 
         * After submitting, go to review page
         * 
         */

        function vi_add_quickstat_calculations() {

            // Display title
            echo $this->vc_title('Add quickstats calculations');

            // Check if reviewed calculation needs editting
            $calculation = NULL;
            if (isset($_SESSION['review_quickstat']) && $_SESSION['review_quickstat'] != NULL) {
                $calculation = $_SESSION['review_quickstat'];
            }

            // Check if reviewed name is saved
            $name = NULL;
            if (isset($_SESSION['review_quickstat_name']) && $_SESSION['review_quickstat_name'] != NULL) {
                $name = $_SESSION['review_quickstat_name'];
            }

            // Display accepted format
            echo 'Format: SUBJECT_ITEM or NUMBER [+-*/] SUBJECT_ITEM or NUMBER';

            // Draw form
            echo '<form method="post" action="index.php?page=1&sub=9&action=4">';
                echo $this->vc_line('Name', '<input type="text" name="name" id="quickstats_calculation" value="'.$name.'" required />');
                echo $this->vc_line('Calculation', '<input type="text" name="calculation" id="quickstats_calculation" value="'.$calculation.'" required />');

                echo $this->vc_add_end('add_calculation');
            echo '</form>';
        }

        /**
         * 
         * Display review quickstats calculations
         * Split items into calculation objects, which removes operators from the original string
         * Get the order of occurence of the operators, based on the original string
         * Loop through calculation objects
         * Check if object is not numeric, and thus an database item (module or ship)
         * If the object is not numeric, split lowercase, which seperates the module/ship from the calculating value
         * Check if the object and calculating value are existing values
         *      1. Make object and database object lowercase for comparisson
         *      2. If object is found, search for item
         *      3. If object or item is not found, display error for review
         * When the check is done, build the calculation review with database items
         *
         */

        function vi_review_quickstat_calculations($name, $calculation) {

            // Display title
            echo $this->vc_title('Review '.$name);
            
            // Split items into calculation objects
            // Matches all *, +, / and -
            $split = preg_split("/[\*\+\/\-]/", $calculation, -1, PREG_SPLIT_DELIM_CAPTURE);

            // Order of operators
            // Matches all *, +, / and -
            preg_match_all("/[\*\+\/\-]/", $calculation, $operators);
            $this->operators = $operators[0];

            // Loop through objects
            foreach($split as $key => $value) {

                // Check if the object in the calculation is found in the database
                $this->quickstats_objects_check($value);
            }

            // Check if all items are found in database
            // If one or more items are not found, display message
            if (isset($this->notfound) && $this->notfound != NULL) {
            
                echo $this->notfound;
            
            } else {

                // Rebuild calculation
                $this->quickstats_rebuild_calculation();
            }

            // Display edit button
            $_SESSION['review_quickstat'] = $_POST['calculation'];
            $_SESSION['review_quickstat_name'] = $_POST['name'];
            echo $this->vc_add_button('Edit calculation', 'index.php?page=1&sub=9&action=3');
            echo $this->vc_add_button('Add calculation', 'index.php?page=1&sub=9&action=2&save=true');
        }

        /**
         *
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * Check if the object in the calculation is found in the database
         * Check if input is numeric or not:
         *      1. Input is not numeric:
         *          Split input into a subject and an item
         *          Loop through modules where a match will be searched
         *          Pass the whole subject array, both subject and item are needed
         *          If no match is found in modules, search in ships
         *          Pass the whole subject array, both subject and item are needed
         *          If stil no match is found, set error
         *      2. Input is numeric:
         *          Add input into array
         *
         */

        function quickstats_objects_check($value) {

            if (!is_numeric($value)) {

                // Seperate object from calculating value
                // $subject[0] contains subject
                // $subject[1] contains item
                $subject = explode('_',$value);

                // Reset found trigger
                // Initial trigger is already 0, but because this is triggered in a loop, a reset is needed
                $this->found = 0;

                // Loop through modules with items
                // Pass $subject item in the loop
                $this->quickstats_loop_module($subject);

                // Loop through ships with items
                // Pass $subject item in the loop
                $this->quickstats_loop_ship($subject);

                // No item found in modules or ships
                if ($this->found == 0) {

                    // Add warning
                    $this->notfound .= '<br>Subject "'.$subject[0].'" with item '.$subject[1].'" is not found, you probably need to review your calculation.<br>';
                
                    // Push empty values into array
                    // Not needed for further processessing as an error is set
                    // Might be used for debuggin
                    array_push($this->array_type, 'none');
                    array_push($this->array_subject, 'none');
                }
            } else {

                // Numeric handling
                $this->quickstats_numeric($value);
            }
        }

        /**
         * 
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * PART OF QUICKSTATS_OBJECTS_CHECK
         * Loop through modules, to find a match for the passed item from the subject
         * Compare module in loop with subject
         * vc_sl needed for space-removing and lowercasing
         * Set found variable to 1 (true), so no further search in ships is triggered
         * Push items in array_type and array_subject
         * Search for the item in module statics
         * If item is not found in statics, search for item in module variables
         * If item is still not found, add error message
         *
         */

        function quickstats_loop_module($subject) {

            // Loop through all modules
            foreach($this->qu_all_modules() as $module_key => $module_value) {
                if ($this->vc_sl($module_value['name']) == $this->vc_sl($subject[0])) {

                    $this->found = 1;
                    
                    // Insert items in array
                    array_push($this->array_type, 'module');
                    array_push($this->array_subject, $module_value['id']);

                    // Reset item found
                    $this->itemfound = 0;

                    // Find and add module static
                    $this->quickstats_module_static($module_value['id'], $subject[1]);

                    // Find and add module variable
                    $this->quickstats_module_variable($module_value['id'], $subject[1]);

                    // If item is not found in statics and variables, display error
                    $this->quickstats_item_not_found($subject[1]);
                }
            }
        }

        /**
         * 
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * PART OF QUICKSTATS_OBJECTS_CHECK
         * Loop through ships, to find a match for the passed item from the subject
         * Compare ship in loop with subject
         * vc_sl needed for space-removing and lowercasing
         * Set found variable to 1 (true), so no error message is triggered
         * Push items in array_type and array_subject
         * Search for the item in ship statics
         * If item is not found in statics, search for item in ship variables
         * If item is still not found, add error message
         *
         */

        function quickstats_loop_ship($subject) {
            
            // Loop through all ships
            foreach($this->qu_all_ships() as $ship_key => $ship_value) {
                if ($this->vc_sl($ship_value['name']) == $this->vc_sl($subject[0])) {
                
                    $this->found = 1;

                    // Insert items in array
                    array_push($this->array_type, 'ship');
                    array_push($this->array_subject, $ship_value['id']);

                    $this->itemfound = 0;

                    // Find and add ship static
                    $this->quickstats_ship_static($ship_id, $item);

                    // Find and add ship variables
                    $this->quickstats_ship_variable($ship_id, $item);

                    // If item is not found in statics and variables, display error
                    $this->quickstats_item_not_found($subject[1]);
                }
            }
        }

        /**
         * 
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * PART OF QUICKSTATS_OBJECTS_CHECK
         * Beforehand there is checked if input was numeric or not, result came back true
         * Add values to arrays
         * 
         */

        function quickstats_numeric($value) {

            array_push($this->array_type, 'numeric');
            array_push($this->array_subject, $value);
            array_push($this->array_item, 'numeric');
            array_push($this->array_varsta, 'none');
        }

        /**
         *
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * PART OF QUICKSTATS_OBJECTS_CHECK
         * PART OF QUICKSTATS_LOOP_MODULE
         * Find and add a module static
         * A match is for the subject is found in modules
         * Loop through all module statics for an match with the subject item
         * If a match is found, add item in arrays
         * Set itemfound to 1, so no error is added
         *
         */

        function quickstats_module_static($module_id, $item) {
            
            // Search item in statics
            foreach($this->qu_all_module_statics_from_module($module_id) as $static_key => $static_value) {
                                    
                // If a match is found, push the items in the arrays, and set itemfound to 1 (true)
                $this->quickstats_push_item($static_value, $item);
            }
        }

        /**
         * 
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * PART OF QUICKSTATS_OBJECTS_CHECK
         * PART OF QUICKSTATS_LOOP_MODULE
         * Find and add module variable
         * A match is for the subject is found in modules
         * Loop through all module variables for an match with the subject item
         * If a match is found, add item in arrays
         * Set itemfound to 1, so no error is added
         * 
         */

        function quickstats_module_variable($module_id, $item) {

            // If item is not found in statics, search in the variables
            if ($this->itemfound == 0) {
                foreach($this->qu_all_module_variables_from_module($module_id) as $variable_key => $variable_value) {
                
                    // If a match is found, push the items in the arrays, and set itemfound to 1 (true)
                    $this->quickstats_push_item($variable_value, $item);
                }
            }
        }

        /**
         * 
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * PART OF QUICKSTATS_OBJECTS_CHECK
         * PART OF QUICKSTATS_LOOP_SHIP
         * Find and add ship static
         * A match is for the subject is found in ships
         * Loop through all ship statics for an match with the subject item
         * If a match is found, add item in arrays
         * Set itemfound to 1, so no further search is needed in variables
         *
         */

        function quickstats_ship_static($ship_id, $item) {
    
            // Search item in statics
            foreach($this->qu_all_ship_statics_from_ship($ship_id) as $static_key => $static_value) {

                // If a match is found, push the items in the arrays, and set itemfound to 1 (true)
                $this->quickstats_push_item($static_value, $item);
            }
        }

        /**
         * 
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * PART OF QUICKSTATS_OBJECTS_CHECK
         * PART OF QUICKSTATS_LOOP_SHIP
         * Find and add ship variable
         * A match is for the subject is found in ships
         * Loop through all ship statics for an match with the subject item
         * If a match is found, add item in arrays
         * Set itemfound to 1, so no error is added
         *
         */

        function quickstats_ship_variable($ship_id, $item) {
            
            // If item is not found in statics, search in the variables
            if ($this->itemfound == 0) {
                foreach($this->qu_all_ship_variables_from_ship($ship_id) as $variable_key => $variable_value) {

                    // If a match is found, push the items in the arrays, and set itemfound to 1 (true)
                    $this->quickstats_push_item($variable_value, $item);
                }
            }
        }

        /**
         * 
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * PART OF QUICKSTATS_OBJECTS_CHECK
         * PART OF QUICKSTATS_LOOP_MODULE AND QUICKSTATS_LOOP_SHIP
         * Add error message
         * Match for module or ship is found
         * No match for an item static of variable is found
         * Add error message
         *
         */

        function quickstats_item_not_found($item) {
            
            // If item is not found in statics and variables, display error
            if ($this->itemfound == 0) {
                $this->notfound .= '<br>Item "'.$item.'" is not found, you probably beed to review your calculation.<br>';
            }
        }

        /**
         * 
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * PART OF QUICKSTATS_OBJECTS_CHECK
         * PART OF QUICKSTATS_MODULE_VARIABLE AND QUICKSTATS_MODULE_STATIC AND QUICKSTATS_SHIP_VARIABLE AND QUICKSTATS_SHIP_STATIC
         * Push static array items
         * Match is found in subject, comparisson for item is needed now
         * Check if item in loop (for modules and ships, for statics and variables) is the same as the input item
         * If a match is found, push item in array (array_item, array_varsta)
         * Set itemfound to 1 (true), so no further search is triggered
         *
         */

        function quickstats_push_item($array, $item) {

            // Check if the item name is the same as the database name
            if ($this->vc_sl($array['name']) == $this->vc_sl($item)) {

                // The items are the same, push items
                array_push($this->array_item, $array['id']);
                array_push($this->array_varsta, 'static');

                // The items are the same, set itemfound 1 (true);
                $this->itemfound = 1;
            }
        }

        /**
         * 
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * Rebuild calculation
         * Loop through all set types of subjects
         *      1. Type is module
         *          Add module to calculation
         *          Add module item to calculation
         *      2. Type is ship
         *          Add ship to calculation
         *          Add ship item to calculation
         *      3. Type is numeric
         *
         */

        function quickstats_rebuild_calculation() {

            // Loop through all subject types
            foreach($this->array_type as $key => $value) {
                
                // Actions based on the type of the subject as stated in array_type
                switch($value) {
                    case 'module':

                        // Set module to calculation
                        $this->quickstats_get_module($this->array_subject[$key]);

                        // Set module item to calculation
                        $this->quickstats_get_module_item($this->array_varsta[$key], $this->array_item[$key]);

                    break;
                    case 'ship':

                        // Set ship to calculation
                        $this->quickstats_get_ship($this->array_subject[$key]);

                        // Set ship item to calculation
                        $this->quickstats_get_ship_item($this->array_varsta[$key], $this->array_item[$key]);

                    break;
                    case 'numeric':
                    
                        // Add number to calculation
                        $this->calculation .= $this->array_subject[$key];

                    break;
                }

                // The if statement lets the final key be skipped, because there is no operator at the end of the calculation
                if (isset($this->operators[$key]) && $this->operators[$key] != NULL) {
                    $this->calculation .= ' '.$this->operators[$key].' ';
                }
            }

            // Echo the final output
            echo $this->vc_line('Calculation',$this->calculation);
        }

        /**
         * 
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * PART OF QUICKSTATS_REBUILD_CALCULATION
         * Add module to calculation
         * Get module and add the name to the calculation
         *
         */

        function quickstats_get_module($subject) {
        
            // Get subject
            $subject = $this->qu_get_module($subject);
            $this->calculation .= $subject['name'];
        }

        /**
         * 
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * PART OF QUICKSTATS_REBUILD_CALCULATION
         * Add module item to calculation
         * 1. If item is static
         *      Get item from static, save it to $item
         * 2. If item is variable
         *      Get item from variable, save it to $item
         * Add item to calculation
         *
         */

        function quickstats_get_module_item($varsta, $item) {

            // Get item
            if ($varsta == 'static') {
                $match = $this->qu_get_module_static($item);
            } elseif($varsta == 'variable') {
                $match = $this->qu_get_module_variable($item);
            }

            // Add item to calculation
            $this->calculation .= ' ('.$match['name'].') ';
        }

        /**
         * 
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * PART OF QUICKSTATS_REBUILD_CALCULATION
         * Add ship to calculation
         * Get ship and add the name to the calculation
         *
         */

        function quickstats_get_ship($subject) {

            // Get subject
            $subject = $this->qu_get_ship($subject);
            $this->calculation .= $subject['name'];
        }

        /**
         * 
         * PART OF REVIEW_QUICKSTAT_CALCULATIONS
         * PART OF QUICKSTATS_REBUILD_CALCULATION
         * Add ship item to calculation
         * 1. If item is static
         *      Get item from static, save it to $item
         * 2. If item is variable
         *      Get item from variable, save it to $item
         * Add item to calculation
         *
         */

        function quickstats_get_ship_item($varsta, $item) {

            // Get item
            if ($varsta == 'static') {
                $match = $this->qu_get_ship_statics($item);
            } elseif($varsta == 'variable') {
                $match = $this->qu_get_ship_variable($item);
            }

            // Add item to calculation
            $this->calculation .= ' ('.$match['name'].') ';
        }
    }
?>