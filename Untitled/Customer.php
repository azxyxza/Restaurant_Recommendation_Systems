<!--Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  This file shows the very basics of how to execute PHP commands
  on Oracle.
  Specifically, it will drop a table, create a table, insert values
  update values, and then query for values

  IF YOU HAVE A TABLE CALLED "Customer" IT WILL BE DESTROYED

  The script assumes you already have a server set up
  All OCI commands are commands to the Oracle libraries
  To get the file to work, you must place it somewhere where your
  Apache server can run it, and you must rename it to have a ".php"
  extension.  You must also change the username and password on the
  OCILogon below to be your ORACLE username and password -->

<html>
    <head>
        <title>CPSC 304 PHP/Oracle Demonstration</title>
    </head>
    <style>
        /* Center align all text */
        body {
            text-align: center;
            background-image: url('image.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            font-family: 'Comic Sans MS', cursive;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 280%;
            background-color: rgba(255, 255, 255, 0.7); /* 50% opacity white */
            z-index: -1;
        }

        /* Improve input field visibility */
        input[type="text"],
        input[type="password"] {
            padding: 12px;
            margin: 8px 0;
            box-sizing: border-box;
            border: 2px solid #ccc;
            border-radius: 4px;
            background-color: #f8f8f8;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        /* Highlight input fields when selected */
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #ff9baf;
            outline: none;
        }

        /* Add some space between form elements */
        .form-element {
            margin-bottom: 10px;
        }

        /* Remove default form button styles */
        input[type="submit"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-color: #ff9baf;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            z-index: 2;
        }

        /* Change form button background color on hover */
        input[type="submit"]:hover {
            background-color: #ff5b5b;
        }
    </style>

    <body>
        <header>
            <h1>Customer Account Management</h1>
        </header>

        <div class="container">
            <section>
                
    <body>
        <h2>Reset</h2>
        <p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>

        <form method="POST" action="Customer.php">
            <!-- if you want another page to load after the button is clicked, you have to specify that page in the action parameter -->
            <input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
            <p><input type="submit" value="Reset" name="reset"></p>
        </form>

        <hr />to 

        <h2>Sign Up New Customer Account</h2>
        <p>The values are case sensitive and if you enter in the wrong case, the update statement will not do anything.</p>
        <form method="POST" action="Customer.php"> <!--refresh page when submitted-->
            <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
             Email: <input type="text" name="Email"> <br /><br />
             ID: <input type="text" name="ID"> <br /><br />
             Password: <input type="text" name="pwd"> <br /><br />
             Address: <input type="text" name="address"> <br /><br />

            <input type="submit" value="Insert" name="insertSubmit"></p>
        </form>

        <hr />
        
        <h2>Back to Log In </h2>
        <form method="POST" action="login.php"> 
            <input type="submit" value="Log In" name="CloginSubmit"></p>
        </form>

        <hr />

        <h2>Change Password in Customer</h2>
        <p>The values are case sensitive and if you enter in the wrong case, the update statement will not do anything.</p>

        <form method="POST" action="Customer.php"> <!--refresh page when submitted-->
            <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
            Email: <input type="text" name="Email"> <br /><br />
            ID: <input type="text" name="id"> <br /><br />
            New Password: <input type="text" name="new_pwd"> <br /><br />

            <input type="submit" value="Update" name="updateSubmit"></p>
        </form>

        <hr />

        <h2>Delete Customer Account</h2>
        <p>You can only delete an exit customer account and once delete, you will need to sign up again to log in</p>

        <form method="POST" action="Customer.php"> <!--refresh page when submitted-->
            <input type="hidden" id="deleteQueryRequest" name="deleteQueryRequest">
            ID: <input type="text" name="id"> <br /><br />
            Email: <input type="text" name="email"> <br /><br />
            Password: <input type="text" name="pwd"> <br /><br />

            <input type="submit" value="Delete" name="deleteSubmit"></p>
        </form>

        <hr />

        <h2>Count the exist Customer Account</h2>
        <form method="GET" action="Customer.php"> <!--refresh page when submitted-->
            <input type="hidden" id="countTupleRequest" name="countTupleRequest">
            <input type="submit" name="countTuples"></p>
        </form>

        <?php
		//this tells the system that it's no longer just parsing html; it's now parsing PHP

        $success = True; //keep track of errors so it redirects the page only if there are no errors
        $db_conn = NULL; // edit the login credentials in connectToDB()
        $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

        function debugAlertMessage($message) {
            global $show_debug_alert_messages;

            if ($show_debug_alert_messages) {
                echo "<script type='text/javascript'>alert('" . $message . "');</script>";
            }
        }

        function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
            //echo "<br>running ".$cmdstr."<br>";
            global $db_conn, $success;

            $statement = OCIParse($db_conn, $cmdstr);
            //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
                echo htmlentities($e['message']);
                $success = False;
            }

            $r = OCIExecute($statement, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
                echo htmlentities($e['message']);
                $success = False;
            }

			return $statement;
		}

        function executeBoundSQL($cmdstr, $list) {
            /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection.
		See the sample code below for how this function is used */

			global $db_conn, $success;
			$statement = OCIParse($db_conn, $cmdstr);

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn);
                echo htmlentities($e['message']);
                $success = False;
            }

            foreach ($list as $tuple) {
                foreach ($tuple as $bind => $val) {
                    //echo $val;
                    //echo "<br>".$bind."<br>";
                    OCIBindByName($statement, $bind, $val);
                    unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
				}

                $r = OCIExecute($statement, OCI_DEFAULT);
                if (!$r) {
                    echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                    $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
                    echo htmlentities($e['message']);
                    echo "<br>";
                    $success = False;
                }
            }
        }

        function printResult($result) { //prints results from a select statement
            echo "<br>Retrieved data from table Customer:<br>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Name</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row["ID"] . "</td><td>" . $row["NAME"] . "</td></tr>"; //or just use "echo $row[0]"
            }

            echo "</table>";
        }

        function connectToDB() {
            global $db_conn;

            // Your username is ora_(CWL_ID) and the password is a(student number). For example,
			// ora_platypus is the username and a12345678 is the password.
            $db_conn = OCILogon("ora_azxyxza", "a97622831", "dbhost.students.cs.ubc.ca:1522/stu");

            if ($db_conn) {
                debugAlertMessage("Database is Connected");
                return true;
            } else {
                debugAlertMessage("Cannot connect to Database");
                $e = OCI_Error(); // For OCILogon errors pass no handle
                echo htmlentities($e['message']);
                return false;
            }
        }

        function disconnectFromDB() {
            global $db_conn;

            debugAlertMessage("Disconnect from Database");
            OCILogoff($db_conn);
        }

        function handleUpdateRequest() {
            global $db_conn;
            
            $email = $_POST['email'];
            $id = $_POST['id'];
            $new_pwd = $_POST['new_pwd'];

            // you need the wrap the old name and new name values with single quotations
            executePlainSQL("UPDATE Customer SET pwd ='" . $new_pwd . "' 
                                    WHERE id ='" . $id . "' AND Email = '" . $email . "'");
            OCICommit($db_conn);
        }

        function handleResetRequest() {
            global $db_conn;
            // Drop old table
            executePlainSQL("DROP TABLE Customer");

            // Create new table
            echo "<br> creating new table <br>";
            executePlainSQL("CREATE TABLE Customer ( Email varchar(50),ID varchar(50),pwd varchar(18), address varchar(50), PRIMARY KEY (Email, ID))");
            OCICommit($db_conn);
        }

        function handleInsertRequest() {
            global $db_conn;

            //Getting the values from user and insert data into the table
            $tuple = array (
                ":email" => $_POST['Email'],
                ":id" => $_POST['ID'],
                ":pwd" => $_POST['pwd'],
                ":address" => $_POST['address']
            );

            $alltuples = array (
                $tuple
            );

            executeBoundSQL("insert into Customer values (:email, :id, :pwd, :address)", $alltuples);
            OCICommit($db_conn);
        }

        function handleCountRequest() {
            global $db_conn;

            $result = executePlainSQL("SELECT Count(*) FROM Customer");

            if (($row = oci_fetch_row($result)) != false) {
                echo "<br> The number of tuples in Customer: " . $row[0] . "<br>";
            }
        }

        function handleDeleteRequest() {
            global $db_conn;

            $id = $_POST['id'];
            $pwd = $_POST['pwd'];
            $email = $_POST['email'];

            $result = executePlainSQL("SELECT COUNT(*) FROM Customer WHERE ID = '" . $id . "' AND Email = '" . $email . "' AND pwd = '" . $pwd . "'");
            $row = oci_fetch_row($result);

            if ($row[0] == 0) {
                echo "Oops! No account found, or you might have input a wrong password! <br>";
            } else {
                // Delete the row with the given ID, email, and password
                executePlainSQL("DELETE FROM Customer WHERE ID = '" . $id . "' AND Email = '" . $email . "' AND pwd = '" . $pwd . "'");
                echo "Account with ID $id and Email $email has been successfully deleted. <br>";
                OCICommit($db_conn);
            }
        }

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('resetTablesRequest', $_POST)) {
                    handleResetRequest();
                } else if (array_key_exists('updateQueryRequest', $_POST)) {
                    handleUpdateRequest();
                } else if (array_key_exists('insertQueryRequest', $_POST)) {
                    handleInsertRequest();
                } else if (array_key_exists('deleteQueryRequest', $_POST)){
                    handleDeleteRequest();
                }
                disconnectFromDB();
            }
        }


        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {
                if (array_key_exists('countTuples', $_GET)) {
                    handleCountRequest();
                }

                disconnectFromDB();
            }
        }

		if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit']) || isset($_POST['deleteSubmit'])) {
            handlePOSTRequest();
        } else if (isset($_GET['countTupleRequest'])) {
            handleGETRequest();
        } 
		?>
	</body>
</html>
