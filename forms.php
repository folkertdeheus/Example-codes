<?php
    class forms extends queries {
        
        ///////////////////////// REDACTED \\\\\\\\\\\\\\\\\\\\\\\\\\\\\

        /**
         *
         * USERS
         * Form used for adding users to database
         *
         * @param string $name               The name of the user
         * @param int    $discord_id         The discord id of the user
         * @param string $permissions_id     The role of the user
         *
         */

        private function add_users($name, $discord_id, $permissions_id) {
           
            // Generate random password
            $pass = substr(md5(// REDACTED \\), 4, 10);
            echo $pass;

            try {
                $this->none(
                    'INSERT INTO `users` (
                        `name`,
                        `discord_id`,
                        `password`,
                        `permissions_id`,
                        `added_by`,
                        `added_on`)
                    VALUES (?, ?, ?, ?, ?, ?)',
                    array(
                        $name,
                        $discord_id,
                        $pass,
                        $permissions_id,
                        $_SESSION['user_id'],
                        date('Y-m-d H:i:s')
                    )
                );
            } catch(Exception $e) {
                echo 'Insert error: '.$e->getMessage();
            }
        }

        /**
         *
         * USERS
         * Form used for editting users to database. This form does not change the password.
         *
         * @param string $name                   The name of the user
         * @param int    $discord_id             The discord id of the user
         * @param string $permissions_id         The role of the user
         * @param int    $artifact_values_id     The artifact_value id to be updated
         *
         */

        private function edit_users($name, $discord_id, $permissions_id, $artifact_values_id) {

            try {
                $this->none(
                    'UPDATE `users` SET
                        `name` = ?,
                        `discord_id` = ?,
                        `permissions_id` = ?,
                        `editted_by` = ?,
                        `editted_on` = ?
                    WHERE `id` = ?',
                    array(
                        $name,
                        $discord_id,
                        $permissions_id,
                        $_SESSION['user_id'],
                        date('Y-m-d H:i:s'),
                        $artifact_values_id
                    )
                );
            } catch(Exception $e) {
                echo 'Insert error: '.$e->getMessage();
            }
        }

        /**
         * 
         * ARTIFACT LEVELS
         * Form used for adding artifact levels to database.
         * Loop through the artifact variables of the sent artifact id, because the form is generated dynamically
         * Set a value for every artifact variable, even if no value is set in the form
         * Add the artifact level in the database. This happens in the foreach loop, so it will execute on every artifact variable
         *
         * @param int    $artifact_id        The id of the artifact the value is bound to
         * @param int    $level              The level of the artifact value
         *
         */

        private function add_artifact_levels($artifact_id, $level) {

            // Loop through artifact variables from sent artifact
            foreach($this->qu_get_all_artifact_variables_from_artifact($artifact_id) as $variable_key => $variable_value) {
                
                // Make sure there is always data to send
                // Empty lines in database can causue tables to display information wrong
                $value = 'No value';
                if (isset($_POST[$variable_value['id']]) && $_POST[$variable_value['id']] != NULL) {
                    $value = $_POST[$variable_value['id']];
                }
                
                // Add artifact level/value
                $this->none(
                    'INSERT INTO `artifacts_values` (
                        `artifacts_variables_id`,
                        `level`,
                        `value`,
                        `added_by`,
                        `added_on`)
                    VALUES (?, ?, ?, ?, ?)',
                    array(
                        $variable_value['id'],
                        $level,
                        $value,
                        $_SESSION['user_id'],
                        date("Y-m-d H:i:s")
                    )
                );
            }
        }

        /**
         * 
         * ARTIFACT LEVELS
         * Form used for editting artifact levels from database.
         * Loop through all POST values, skip values that are no artifact variable values
         * Check if the current artifact variable in the loop already exists in the database
         * Create a new artifact level/value if it did not exist yet
         * Update the existing one if there is one
         *
         * @param int    $level                  The level of the artifact value
         *
         */

        private function edit_artifact_levels($level) {

            // Loop through all post fields
            foreach($_POST as $key => $value) {

                // Form fields to skip
                $skip = array('level', 'form', 'artifact_id', 'editted_on', 'editted_by');
                
                if (!in_array($key, $skip)) {

                    // Count if values are already in database. If so, update. If not, insert
                    $count = $this->qu_count_values_from_variable_and_level($key, $level);

                    if ($count > 0) {
                        
                        // Update existing artifact level/value
                        $this->none(
                            'UPDATE `artifacts_values` SET
                                `level` = ?,
                                `value` = ?,
                                `editted_by` = ?,
                                `editted_on` = ?
                            WHERE `artifact_id` = ?
                            AND `level` = ?',
                            array(
                                $level,
                                $value,
                                $_SESSION['user_id'],
                                date("Y-m-d H:i:s"),
                                $key,
                                $level
                            )
                        );
                    } else {

                        // Create new artifact level/value
                        $this->none(
                            'INSERT INTO `artifacts_values` (
                                `artifacts_variables_id`,
                                `level`,
                                `value`,
                                `added_by`,
                                `added_on`)
                            VALUES (?, ?, ?, ?, ?)',
                            array(
                                $key,
                                $_POST['level'],
                                $value,
                                $_POST['editted_by'],
                                $_POST['editted_on']
                            )
                        );
                    }
                }
            }
        }
    }

    ///////////////////////// REDACTED \\\\\\\\\\\\\\\\\\\\\\\\\\\\\
?>
