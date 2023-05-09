<!--Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  This file shows the very basics of how to execute PHP commands
  on Oracle.
  Specifically, it will drop a table, create a table, insert values
  update values, and then query for values

  IF YOU HAVE A TABLE CALLED "Business Owner" IT WILL BE DESTROYED

  The script assumes you already have a server set up
  All OCI commands are commands to the Oracle libraries
  To get the file to work, you must place it somewhere where your
  Apache server can run it, and you must rename it to have a ".php"
  extension.  You must also change the username and password on the
  OCILogon below to be your ORACLE username and password -->

  <html>
    <head>
        <title>CPSC 304 PHP Project team 58</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
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
    </head>
    
    <body>
        <header>
            <h1>Vancouver Restaurant System Login Page</h1>
        </header>

        <hr />

        <div class="container">
            <section>          
                <h2>Customer Log In </h2>
                <form method="POST"> <!--refresh page when submitted-->
                    <input type="hidden" id="CLoginRequest" name="CLoginRequest">
                    Email: <input type="text" name="c_email"> <br /><br />
                    ID: <input type="text" name="c_id"> <br /><br />
                    Password: <input type="password" name="c_pwd"> <br /><br />
                    <input type="submit" value="Customer Log In" name="CloginSubmit"></p>
                </form>

                <hr />

                <h2>Business Owner Log In </h2>
                <form method="POST"> <!--refresh page when submitted-->
                    <input type="hidden" id="BLoginRequest" name="BLoginRequest">
                    Email: <input type="text" name="email"> <br /><br />
                    ID: <input type="text" name="id"> <br /><br />
                    Password: <input type="password" name="pwd"> <br /><br />
                    <input type="submit" value="Business Owner Log In" name="BloginSubmit"></p>
                </form>

                <hr />

                <h2>Customer Sign Up / Account Management</h2>
                <form method="POST" action="Customer.php"> <!--refresh page when submitted-->
                    <input type="submit" value="Customer" name="CM"> 
                </form>
                <hr />

                <h2>Business Owner Sign Up / Account Management</h2>
                <form method="POST" action="BusinessOwner.php"> <!--refresh page when submitted-->
                    <input type="submit" value="Business Owner" name="BO"> 
                </form>
            </section>
        </div>
    </body>
</html>

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


        function CLoginRequest() {
            global $db_conn;

            $id = $_POST['c_id'];
            $pwd = $_POST['c_pwd'];
            $email = $_POST['c_email'];

            $result = executePlainSQL("SELECT COUNT(*) FROM Customer WHERE ID = '" . $id . "' AND Email = '" . $email . "' AND pwd = '" . $pwd . "'");
            $row = oci_fetch_row($result);

            if ($row[0] == 0) {
                echo "Oops! No exist account or wrong password! <br>";
            } else {
                // header("Location: userInterface.php");
                echo "<meta http-equiv='refresh' content='0;url=userInterface.php'>";
                OCICommit($db_conn);
            }
        }

        function BLoginRequest() {
            global $db_conn;

            $id = $_POST['id'];
            $pwd = $_POST['pwd'];
            $email = $_POST['email'];

            $result = executePlainSQL("SELECT COUNT(*) FROM Business_Owner WHERE ID = '" . $id . "' AND Email = '" . $email . "' AND pwd = '" . $pwd . "'");
            $row = oci_fetch_row($result);

            if ($row[0] == 0) {
                echo "Oops! No exist account or wrong password! <br>";
            } else {
                // header("Location: userInterface.php");
                echo "<meta http-equiv='refresh' content='0;url=userInterface.php'>";
                OCICommit($db_conn);
            }
        }

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('CLoginRequest', $_POST)){
                    CLoginRequest();
                } else if (array_key_exists('BLoginRequest', $_POST)){
                    BLoginRequest();
                }
                disconnectFromDB();
            }
        }


        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.

		if ( isset($_POST['CloginSubmit'])|| isset($_POST['BloginSubmit'])) {
            handlePOSTRequest();
        }  
		?>
	</body>
</html>
