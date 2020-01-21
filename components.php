<?php
    /**
     *
     * components class is used for drawing visual items which are often needed
     *        
     */

    class components extends queries {

        /**
         *
         * FUNCTIONAL COMPONENT
         * This function is used for checking the permissions of the user
         * Check if the user_id session exists (user is logged in)
         * Get all information from the user, which includes the permission id (role)
         * Get all permissions from that id
         * Check for the requested permission
         * Return TRUE if requested permission is set to 1 in the database, otherwise return false
         *
         * @param string $permission         The name of the permission the user should have to continue
         *                                   These permission names are found in the database
         *                                   They should be the same as the column names in the permssion table
         *
         * @return boolean
         *
         */

        function vc_rights($permission) {

            if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != NULL) {

                $user = $this->qu_get_user($_SESSION['user_id']);
                $permissions = $this->qu_get_permission($user['permissions_id']);

                if ($permissions[$permission] == 1) {
                    return true;
                }
            }

            return false;
        }

        /**
         *
         * FUNCTIONAL COMPONENT
         * This function is used to switch from artifact id's to the name of the artifact
         * This function is called in the experimental console function of the page, specifically the trade command
         *
         * @param int    $artifact       The number of the artifact
         *
         * @return string
         *
         */

        function vc_art_switch($artifact) {

            switch($artifact) {
                case '0':
                    return 'All';
                break;
                case '1':
                    return 'Orbs';
                break;
                case '2':
                    return 'Blue crystals';
                break;
                case '3':
                    return 'Tetrahedrons';
                break;
            }
        }

        /**
         *
         * FUNCTIONAL COMPONENT
         * This function converts strings to spaceless, lowercased strings
         * This is used for comparing user input and database items
         *
         * @param string $item       The item to convert
         *
         * @return string
         *
         */

        function vc_sl($item) {

            return strtolower(str_replace(' ','',$item));
        }

        /**
         *
         * FORM COMPONENT
         * This function is used for drawing SELECT form fields
         * Draw a table line with a left and right field
         * Left field is used for the field name
         * Right field is used for the SELECT field
         * Line has fixed width, defined in the css
         *
         * @param string $name       display name of the select field
         * @param string $id         form name of the select field
         * @param array  $array      MULTIDIMENSIONAL select content, with original database content, which at least contain a "name" and "id" key
         *
         * @return string
         *
         */

        function vc_select($name, $id, $array) {

            $return = '<div class="line">';
                $return .= '<div class="left">';
                    $return .= $name;
                $return .= '</div> <!-- END LINE LEFT -->';
                $return .= '<div class="right">';
                    $return .= '<select name="'.$id.'">';
                        foreach($array as $key => $value) {
                            $return .= '<option value="'.$value['id'].'">'.$value['name'].'</option>';
                        }
                    $return .= '</select>';
                $return .= '</div> <!-- END LINE RIGHT -->';
            $return .= '</div> <!-- END LINE -->';

            return $return;
        }

        /**
         *
         * FORM COMPONENT
         * This function is used for drawing SELECT form fields, where one item is selected
         * Draw a table line with a left and right field
         * Left field is used for the field name
         * Right field is used for the SELECT field
         * Line has fixed widths, defined in the css
         *
         * @param string $name       display name of the select field
         * @param string $id         form name of the select field
         * @param array  $array      MULTIDIMENSIONAL select content, with original multidimensional database content which at least contain a "name" and "id" key
         * @param int    $selected   the id of the selected field. The id should match one item in $array
         *
         * @return string
         *
         */

        function vc_selected($name, $id, $array, $selected) {

            $return = '<div class="line">';
                $return .= '<div class="left">';
                    $return .= $name;
                $return .= '</div> <!-- END LINE LEFT -->';
                $return .= '<div class="right">';
                    $return .= '<select name="'.$id.'">';
                        foreach($array as $key => $value) {
                            $return .= '<option value="'.$value['id'].'" ';
                            if ($value['id'] == $selected) {
                                $return .= 'selected';
                            }
                            $return .= '>'.$value['name'].'</option>';
                        }
                    $return .= '</select>';
                $return .= '</div> <!-- END LINE RIGHT -->';
            $return .= '</div> <!-- END LINE -->';

            return $return;
        }

        /**
         *
         * FORM COMPONENT
         * This function is used for checking the value of checkboxes and radio buttons
         * Primarily used for edit forms where the database stores 0 (false) and 1 (true)
         * Returns "checked" if $boolean is 1
         *
         * @param int    $boolean        The value that should be checked
         *
         * @return string
         *
         */

        function vc_check_boolean($boolean) {

            if ($boolean == 1) {
                return 'checked';
            }
        }

        /**
         *
         * FORM COMPONENT
         * This function is used for adding the formname to the form
         * The formname is a hidden field, where the value can be accessed by $_POST['form']
         *
         * @param string $name       The name of the form
         *                           This value should be the same as in the forms_switch document, where the forms are sorted before handling
         *
         * @return string
         *
         */

        function vc_formname($name) {

            return '<input type="hidden" name="form" value="'.$name.'" />';
        }

        /**
         *
         * FORM COMPONENT
         * This function is used for returning the save button
         *
         * @return string
         *
         */

        function vc_save() {

            return $this->vc_line('','<input type="submit" value="Save" />');   
        }

        /**
         *
         * FORM COMPONENT
         * This functions combines all the add form end elements
         *
         * @param string $formname           The name of the form
         *                                   This should be the same as in the forms_switch class, where the form sorting before the handling is done
         *
         * @return string
         *
         */

        function vc_form_end($formname) {

            $return = $this->vc_formname($formname);
            $return .= $this->vc_save();

            return $return;
        }

        /**
         *
         * MENU COMPONENT
         * This function is used for drawing a single menu item in the main menu
         *
         * @param string $link           The destination link of the menuitem
         *                               (For using submenu items every menu link should have a unique ?page= variable)
         * @param string $name           The name of the menu item
         *
         * @return string
         *
         */

        function vc_menuitem($link, $name) {

            $return = '<div class="menuitem">';
                $return .= '<a href="'.$link.'">'.$name.'</a>';
            $return .= '</div> <!-- END MENUITEM -->';

            return $return;
        }

        /**
         *
         * MENU COMPONENT
         * This function is used for drawing a single sub menu item in the main menu
         * Check if the requested page is the active page, so submenu items are only visible if main item is active
         *
         * @param string $link       The destination link of the submenuitem
         * @param string $name       The name of the submenuitem
         * @param int    $page       The pagenumber of the main menu item (assuming that every link uses a ?page=)
         *
         * @return string
         *
         */

        function vc_submenuitem($link, $name, $page) {

            if (isset($_GET['page']) && $_GET['page'] == $page) {
                $return = '<div class="submenuitem">';
                    $return .= '<a href="'.$link.'">'.$name.'</a>';
                $return .= '</div> <!-- END SUBMENUITEM -->';

                return $return;
            }
        }

        /**
         *
         * VISUAL COMPONENT
         * This function is used to return the title div
         *
         * @param string $title          The title of the page
         *
         * @return string
         *
         */

        function vc_title($title) {

            $return = '<div class="title">';
            $return .= $title;
            $return .= '</div>';

            return $return;
        }

        /**
         *
         * VISUAL COMPONENT
         * Mostly used for drawing formfields
         * Draw a table line with a left and a right field
         * Left field is used for the field name
         * Right field is used for INPUT (or TEXTAREA) field
         * Line has fixed widths, defined in the css
         *
         * @param string $name           display name of the (form) field
         * @param string $field          content of the field (mostly an html input tag)
         *
         * @return string
         *
         */

        function vc_line($name, $field) {

            $return = '<div class="line">';
                $return .= '<div class="left">';
                    $return .= $name;
                $return .= '</div> <!-- END LINE LEFT -->';
                $return .= '<div class="right">';
                    $return .= $field;
                $return .= '</div> <!-- END LINE RIGHT -->';
            $return .= '</div> <!-- END LINE -->';

            return $return;
        }

        /**
         *
         * VISUAL COMPONENT
         * This function is used for drawing a div that behaves like a table line
         * This is mostly used in combination with floating divs, because the tableline div clears the float
         *
         * @param string $content        This can be anything, but mostly contains floating divs
         *
         * @return string
         *
         */

        function vc_table_line($content) {

            $return = '<div class="tableline">';
            $return .= $content;
            $return .= '</div>';

            return $return;
        }
        
        /**
         *
         * VISUAL COMPONENT
         * This function returns a neutral button
         *
         * @param string $name       The name of the button
         * @param string $link       The destination link of the button
         *
         * @return string
         *
         */

        function vc_button($name, $link) {

            $return = '<div class="button">';
                $return .= '<a href="'.$link.'">';
                    $return .= $name;
                $return .= '</a>';
            $return .= '</div> <!-- END BUTTON -->';

            return $return;
        }

        /**
         *
         * VISUAL COMPONENT
         * This function is used to return an "Add" button
         *
         * @param string $name           The name of the button
         * @param string $link           The destination link of the button
         *
         * @return string
         *
         */

        function vc_add_button($name, $link) {

            $return = '<br>';
            $return .= '<div class="addbutton">';
                $return .= '<a href="'.$link.'">';
                    $return .= $name;
                $return .= '</a>';
            $return .= '</div> <!-- END ADD BUTTON -->';

            return $return;
        }

        /**
         *
         * VISUAL COMPONENT
         * This function is used to return a "Delete" button
         *
         * @param string $name       The name of the button
         * @param string $link       The destination link of the button
         *
         * @return string
         *
         */

        function vc_delete_button($name, $link) {

            $return = '<div class="deletebutton tableline">';
                $return .= '<a href="'.$link.'">';
                    $return .= $name;
                $return .= '</a>';
            $return .= '</div> <!-- END DELETE BUTTON -->';

            return $return;
        }

        /**
         *
         * VISUAL COMPONENT
         * This function is used to return a "Yes" button
         * This button is used in confirmation of deletion
         *
         * @param string $link           The destination link of the button
         *
         * @return string
         *
         */

        function vc_yes_button($link) {

            $return = '<div class="yesbutton">';
                $return .= '<a href="'.$link.'">Yes</a>';
            $return .= '</div> <!-- END ADD BUTTON -->';

            return $return;
        }

        /**
         *
         * VISUAL COMPONENT
         * This function is used to return a "No" button
         * This button is used in denying a deletion
         *
         * @param string $link       The destination link of the button
         *
         * @return string
         *
         */

        function vc_no_button($link) {

            $return = '<div class="nobutton">';
                $return .= '<a href="'.$link.'">No</a>';
            $return .= '</div> <!-- END ADD BUTTON -->';

            return $return;
        }

        /**
         *
         * VISUAL COMPONENT
         * This function combines the "Yes" and "No" button
         *
         * @param string $yes_link       The destination link of the "Yes" button
         * @param string $no_link        The destination link of the "No" button
         *
         * @return string
         *
         */

        function vc_yes_no($yes_link, $no_link) {

            $return = '<div class="tableline">';
                $return .= $this->vc_yes_button($yes_link);
                $return .= $this->vc_no_button($no_link);
            $return .= '</div>';

            return $return;
        }

        /**
         *
         * VISUAL COMPONENT
         * This function returns small (sub)titles with custom colors
         *
         * @param string $title      The name of the title
         * @param string $color      The color in which the title needs to be displayed
         *                           Accepts everything the CSS command "color:" accepts (No checking on proper value is done)
         *
         * @return string
         *
         */

        function vc_small_title($title, $color) {

            $return = '<style>';
                $return .= '.small_title_'.$title.' {';
                    $return .= 'color: '.$color.';';
                    $return .= 'border-bottom: 1px solid '.$color.';';
                $return .= '}';
            $return .= '</style>';

            $return .= '<div class="small_title_'.$title.' small_title">';
                $return .= $title;
            $return .= '</div> <!-- END SMALL TITLE -->';

            return $return;
        }
    }
?>