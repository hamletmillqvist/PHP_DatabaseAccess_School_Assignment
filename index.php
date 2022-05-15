<?php
    // Thank you, W3School for this example!
    // https://www.w3schools.com/php/php_form_validation.asp
    function sanitizeVariable($var) {
        $var = trim($var);
        $var = stripslashes($var);
        $var = htmlspecialchars($var);
        return $var;
    }

    function parsePOSTArg($arg) {
        if (isset($_POST[$arg])) {
            return sanitizeVariable($_POST[$arg]);
        } else {
            return null;
        }
    }

    function parseGETArg($arg) {
        if (isset($_GET[$arg])) {
            return sanitizeVariable($_GET[$arg]);
        } else {
            return null;
        }
    }

    function getStatusString($status) {
        switch ($status) {
            case '0LateNoReview': return 'Not Reviewed [LATE]';
            case '1LateAdmitted': return 'Admitted [LATE]';
            case '2NoReview': return 'Not reviewed';
            case '3Admitted': return 'Admitted';
            case '4Delivered': return 'Delivered';
            case '5Denied': return 'Denied';
            default: return null;
        }
    }

    $servername = "localhost";
    $username = "sqllab";
    $password = "Tomten2009"; // Yes I know. This is for a locally run database during the assignment anyway.
    $database = "test_database";

    try {
        $dbo = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
        // set the PDO error mode to exception
        $dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Failed dboecting to database: " . $e->getMessage();
        die();
    }

    if (parseGETArg('downloadwish') !== null) {
        $ref = parseGETArg('ref');
        $yr = parseGETArg('yr');

        if (empty($ref) || empty($yr)) {
            echo "Missing parameters!";
            exit;
        }

        $query = 
"
select wd.text as txt
from Wishlist
	left join WishlistDescription as wd
		on wd.Id = Wishlist.Id
		and wd.Year = Wishlist.Year

where wd.Year = '".$yr."' and wd.Id = '".$ref."'
limit 1;
";
        $file_content = null;

        try {
            $result = $dbo->query($query);
            $file_content = $result->fetch()['txt'];
        } catch(PDOException $e) {
            echo $e->getMessage();
            exit;
        }            

        $file_name = 'wishlist.txt';

        header("Content-Type: text/plain");
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header("Content-Length: " . strlen($file_content));
        echo $file_content;
        exit;
    }

    $f1_pid = parsePOSTArg('f1_pid');
    $f1_firstName = parsePOSTArg('f1_firstName');
    $f1_lastName = parsePOSTArg('f1_lastName');
    $f1_birthYear = parsePOSTArg('f1_birthYear');
    $f1_regionCode = parsePOSTArg('f1_regionCode');
    $f1_wishText = parsePOSTArg('f1_wishText');
    $f1_submit = parsePOSTArg('f1_submit');

    $f2_sleighNumber = parsePOSTArg('f2_sleighNumber');
    $f2_regionCode = parsePOSTArg('f2_regionCode');
    $f2_submit = parsePOSTArg('f2_submit');

    $f3_referenceId = parsePOSTArg('f3_referenceId');
    $f3_yearSearch = parsePOSTArg('f3_yearSearch');
    $f3_admitWish = parsePOSTArg('f3_admitWish');
    $f3_denyWish = parsePOSTArg('f3_denyWish');

    $f4_submit = parsePOSTArg('f4_submit');
    $f4_sleighSelect = parsePOSTArg('f4_sleighSelect');

    $f4_checked = [];
    if (isset($_POST['f4_checked'])) {
        $i = 0;
        foreach ($_POST['f4_checked'] as $value) {
            $f4_checked[$i] = sanitizeVariable($value);
            $i++;
        }
    }
    

    // FORM 1
    if (isset($f1_submit)) {
        if (!empty($f1_pid) && !empty($f1_firstName) && !empty($f1_lastName) && !empty($f1_birthYear) && !empty($f1_wishText) && !empty($f1_regionCode)) {
            // Perform insert
            $query = 'call MakeWish("' . $f1_pid . '", "' . $f1_firstName . '", "' . $f1_lastName . '", ' . $f1_birthYear . ', "' . $f1_regionCode . '", "' . $f1_wishText . '", STR_TO_DATE("2021,12,12", "%Y,%m,%d"));';

            try {
                $result = $dbo->query($query);
                echo '<script>alert("Thank you for your submission!");</script>';
            }  catch(PDOException $e) {
                echo '<script>alert("FAILURE! '.$e->GetMessage().'");</script>';
            }
        } else {
            echo $f1_pid." ".$f1_firstName." ".$f1_lastName." ".$f1_birthYear." ".$f1_wishText." ".$regionCode;
            echo '<script>alert("Please fill ALL required fields!")</script>';
        }
    }

    // FORM 2
    if (isset($f2_submit)) {
        if (!empty($f2_regionCode) && !empty($f2_sleighNumber)) {
            $query =
"
call MoveSleigh(" . $f2_sleighNumber . ", '" . $f2_regionCode . "');
";
            try {
                $result = $dbo->query($query);
                echo '<script>alert("Sleigh #' . $f2_sleighNumber . ' has been moved to the new region!");</script>';
            }  catch(PDOException $e) {
                echo '<script>alert("FAILURE! '.$e->GetMessage().'");</script>';
            }
        } else {
            echo '<script>alert("Please fill ALL required fields!")</script>';
        }
    }

    // FORM 3
    if (isset($f3_yearSearch) && strlen($f3_yearSearch) < 1) {
        unset($f3_yearSearch);
    } else {
        $_POST['f3_yearSearch'] = $f3_yearSearch;
    }

    if ($f3_admitWish !== null) {
        $query =
"
call AdmitWish('".$f3_referenceId."',".$f3_yearSearch.",1);
";
        try {
            $result = $dbo->query($query);
        }  catch(PDOException $e) {
            echo '<script>alert("FAILURE! '.$e->GetMessage().'");</script>';
        }
    } else if ($f3_denyWish !== null) {
        $query =
"
call AdmitWish('".$f3_referenceId."',".$f3_yearSearch.",0);
";
        try {
            $result = $dbo->query($query);
        }  catch(PDOException $e) {
            echo '<script>alert("FAILURE! '.$e->GetMessage().'");</script>';
        }
    }

    if ($f4_submit !== null) {
        if (!empty($f4_sleighSelect) && count($f4_checked) > 0) {
            $count = 0;
            foreach ($f4_checked as $value) {
                $split = explode("Y",$value);
                $id = $split[0];
                $year = $split[1];

                $query =
"
insert into Parcel (SleighNr, WishlistYear, WishlistId) values (".$f4_sleighSelect.", ".$year.", '".$id."');
";
                try {
                    $result = $dbo->query($query);
                    $count++;
                }  catch(PDOException $e) {
                    echo '<script>alert("FAILURE! '.$e->GetMessage().'");</script>';
                }
            }
            echo '<script>alert("Successfully created '.$count.' new parcels!");</script>';
        }
    }
?>

<html>
    <head>
        <link rel="stylesheet" href="styles.css">
        <script>
            function dlWish(ref, yr) {
                window.open("index.php?downloadwish=true&ref="+ref+"&yr="+yr);
            }
        </script>
    </head>
    <body>
        <h1>Assignment 3 (H5-H8)</h1>
        <p id='response'></p>

        <div id='content'>
            <div id='form1' class='submission'>
                <div id='form1_top'>
                    <h2>Submit new wishlist</h2>
                    <p class='req'>Required fields *</p>

                    <form action="index.php" method="POST">
                        <div class='inputField'>
                            <div>Personal ID: <span class='req'>*</span></div>
                            <input type="text" name='f1_pid' placeholder="XXXX-RRRRRR-mm-yy" value="<?php echo $f1_pid ?>">
                        </div> <!-- #inputField -->

                        <div class='inputField'>
                            <div>First Name: <span class='req'>*</span></div>
                            <input type="text" name='f1_firstName' value="<?php echo $f1_firstName ?>">
                        </div>  <!-- #inputField -->

                        <div class='inputField'>
                            <div>Last Name: <span class='req'>*</span></div>
                            <input type="text" name='f1_lastName' value="<?php echo $f1_lastName ?>">
                        </div> <!-- #inputField --> 

                        <div class='inputField'>
                            <div>Birth Year: <span class='req'>*</span></div>
                            <input type="number" name='f1_birthYear' placeholder="yyyy" value="<?php echo $f1_birthYear ?>">
                        </div> <!-- #inputField -->

                        <div class='inputField'>
                            <div>Region: <span class='req'>*</span></div>
                                <select name="f1_regionCode" id='regionSelect'>
                                <option value="">--Choose a region--</option>
                                <?php
                                // AUTO GENERATED OPTIONS FROM ALL AVAILABLE REGIONS
                                    try {
                                        $query = "select Code, CountryName from Region;";
                                        $result = $dbo->query($query);
                                        foreach ($result as $row) {
                                            if ($row['Code'] == $f1_regionCode) {
                                                echo '<option selected value="' . $row['Code'] . '">' . $row['CountryName'] . '</option>';
                                            } else {
                                                echo '<option value="' . $row['Code'] . '">' . $row['CountryName'] . '</option>';
                                            }
                                        }
                                    }  catch(PDOException $e) {
                                        echo "FAILURE!";
                                        echo $e->GetMessage();
                                    }
                                ?>
                            </select>
                        </div> <!-- #inputField -->
                        
                        <div class='inputField'>
                            <div>Write wish here: <span class='req'>*</span></div>
                            <textarea id='wishInputField' name='f1_wishText' placeholder="Dear Santa, For this year I wish..."><?php
                                    if (!empty($f1_wishText)) {
                                        echo $f1_wishText;
                                    }
                                ?></textarea>
                        </div>  <!-- #inputField -->

                        <div class='makeCentered'>
                            <input type="submit" name="f1_submit" value="Send Wishlist to Santa!">
                        </div> <!-- Centered item -->
                    </form>  <!-- Make wish -->

                    <p id='resultMessage'>
                    </p> <!-- #resultMessage -->
                </div> <!-- #form1_top -->
                <div id='form1_bottom'>
                    <h2>Wishlist table contents:</h2>
                    <table class='stripedTable'>
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Year Submitted</th>
                            <th>Admitted</th>
                            <th>Year Delivered</th>
                        </tr>
                        <?php
                            $query = 
"
select 
    c.id as 'id',
    concat(c.firstName, ' ', c.lastName) as 'name',
    w.year as 'year', 
    (case 
        when w.admitted = 1 then 'Yes'
        when w.admitted = 0 then 'No'
        else '-'
    end) as 'admitted',
    w.yearDelivered as 'yearDelivered'
from Wishlist as w 
    left join Child as c 
        on w.Id = c.Id 
    left join WishlistDescription as wd
        on wd.Id = w.Id and wd.Year = w.Year
order by year desc;
";
                            try {   
                                $result = $dbo->query($query);

                                foreach ($result as $row) {
                                    echo "<tr>";
                                    echo "<td>".$row['id']."</td>";
                                    echo "<td>".$row['name']."</td>";
                                    echo "<td>".$row['year']."</td>";
                                    echo "<td>".$row['admitted']."</td>";
                                    echo "<td>".$row['yearDelivered']."</td>";
                                    echo "</tr>";
                                }
                            }  catch(PDOException $e) {
                                echo "FAILURE!";
                                echo $e->GetMessage();
                            }
                        ?>
                    </table>
                </div>  <!-- #form1_bottom -->
            </div> <!-- #form1 -->
            <div id='content_right'>
                <div id='form2' class="submission">
                    <h2>Update sleigh region</h2>
                    <p class='req'>Required fields *</p>
                    <div id='sleigh_content'>
                        <div id='sleigh_actions'>
                            <form  action="index.php" method="POST">
                                <div class='inputField'>
                                    <div>Select sled to move: <span class='req'>*</span></div>
                                        <select name="f2_sleighNumber" id='regionSelect'>
                                        <option value="">--Choose a sleigh--</option>
                                        <?php
                                        // AUTO GENERATED OPTIONS FROM ALL AVAILABLE REGIONS
                                            $query = 
"
select 
    Sleigh.Nr as 'Number',
    Sleigh.Name as 'Name',
    Region.CountryName as 'Region'

from Sleigh
    left join Region
        on Sleigh.RegionCode = Region.Code
";
                                            try {
                                                $result = $dbo->query($query);
                                                foreach ($result as $row) {
                                                    if ($row['Number'] == $f2_sleighNumber) {
                                                        echo '<option selected value="' . $row['Number'] . '">#' . $row['Number'] . ' - ' . $row['Name'] . ' (' . $row['Region'] . ')</option>';
                                                    } else {
                                                        echo '<option value="' . $row['Number'] . '">#' . $row['Number'] . ' - ' . $row['Name'] . ' (' . $row['Region'] . ')</option>';
                                                    }
                                                }
                                            }  catch(PDOException $e) {
                                                echo "FAILURE!";
                                                echo $e->GetMessage();
                                            }
                                        ?>
                                    </select>
                                </div> <!-- #inputField -->

                                <div class='inputField'>
                                    <div>Select target region: <span class='req'>*</span></div>
                                        <select name="f2_regionCode" id='regionSelect'>
                                        <option value="">--Choose a region--</option>
                                        <?php
                                            // AUTO GENERATED OPTIONS FROM ALL AVAILABLE REGIONS
                                            $query = 
"
select Code, CountryName from Region;
";
                                            try {
                                                $result = $dbo->query($query);
                                                foreach ($result as $row) {
                                                    if ($row['Code'] == $regionCode) {
                                                        echo '<option selected value="' . $row['Code'] . '">' . $row['CountryName'] . '</option>';
                                                    } else {
                                                        echo '<option value="' . $row['Code'] . '">' . $row['CountryName'] . '</option>';
                                                    }
                                                }
                                            }  catch(PDOException $e) {
                                                echo "FAILURE!";
                                                echo $e->GetMessage();
                                            }
                                        ?>
                                    </select>
                                </div> <!-- #inputField -->
                                
                                <br>
                                <br>
                                <br>
                                <div class='makeCentered'>
                                    <input type="submit" name="f2_submit" value="Move sleigh">
                                </div> <!-- Centered item -->
                            </form>
                        </div> <!-- #sleigh_actions -->

                        <div id='sleigh_display'>
                            <div class='makeCentered'>
                                <h4>Current Sleigh Data</h4>
                            </div> <!-- Centered item -->

                            <div class='makeCentered'>
                                <table  class='stripedTable'>
                                    <tr>
                                    <th>Number</th>
                                    <th>Name</th>
                                    <th>Location</th>
                                    </tr>
                                    <?php
                                        $query = 
"
select 
    Sleigh.Nr as 'Number',
    Sleigh.Name as 'Name',
    Region.CountryName as 'Region'

from Sleigh
    left join Region
        on Sleigh.RegionCode = Region.Code

        order by Number asc;
";
                                        try {
                                            $result = $dbo->query($query);

                                            foreach ($result as $row) {
                                                echo "<tr>";
                                                echo "<td>".$row['Number']."</td>";
                                                echo "<td>".$row['Name']."</td>";
                                                echo "<td>".$row['Region']."</td>";
                                                echo "</tr>";
                                            }
                                        }  catch(PDOException $e) {
                                            echo "FAILURE!";
                                            echo $e->GetMessage();
                                        }
                                    ?>
                                </table>
                            </div> <!-- Centered item -->
                        </div> <!-- #sleigh_display -->
                    </div> <!-- #sleight_content -->
                </div> <!-- #form2 -->
                <div id='form3'  class="submission">
                    <h2>Delivery Lookup</h2>
                    <form action="index.php" method="post">
                        <div class='inputField'>
                            <div>Year: </div>
                            <div class='horizontal_children'>
                                <input type="number" name='f3_yearSearch' placeholder="yyyy" value="<?php echo $f3_yearSearch ?>">
                                <input type="submit" name="f3_submit" value="Search" id='f3_search'>
                            </div> <!-- horizontal_children -->
                        </div> <!-- #inputField -->
                    </form>

                    
                    <?php
                        if (isset($f3_yearSearch) && !isset($f3_referenceId)) {
                            $query =
"
select 
	Wishlist.Id as 'Reference',    
    (case
        when Wishlist.YearDelivered is null then (case 
            when Wishlist.Admitted = 0 then '5Denied'
            else  (case
                when Wishlist.Year < year(now()) then (case
                    when Wishlist.Admitted = 1 then '1LateAdmitted'
                    else '0LateNoReview'
                end)
                else (case
                    when Wishlist.Admitted = 1 then '3Admitted'
                    else '2NoReview'
                end)
            end)
        end)
        else '4Delivered'
    end) as Status
from Wishlist
where Wishlist.Year = '" . $f3_yearSearch . "'
order by Status;
";
                            try {
                                $result = $dbo->query($query);

                                echo "Found " . $result->rowCount() . " results for the year " . $f3_yearSearch . ".";

                                if ($result->rowCount() > 0) {
                                    echo 
"
<br><br>
<table id='colorKeys'>
    <tr>
        <td>Not Reviewed <span id='coloredBox'  class='status2NoReview'></span></td>
        <td>Not Reviewed & Late <span id='coloredBox'  class='status0LateNoReview'></span></td>
        <td>Delivered <span id='coloredBox'  class='status4Delivered'></span></td>
    </tr>
    <tr>
        <td>Admitted <span id='coloredBox'  class='status3Admitted'></span></td>
        <td>Admitted & Late <span id='coloredBox'  class='status1LateAdmitted'></span></td>
        <td>Denied <span id='coloredBox'  class='status5Denied'></span></td>
    </tr>
</table>
<br>
";
                                }

                                echo "<table class='listTable'>";
                                foreach ($result as $row) {
                                    echo "<tr class=status" . $row['Status'] . ">";
                                    echo "<form ation='index.php' method='post'>";
                                    echo "<td><input type='hidden' name='f3_yearSearch' value='" . $f3_yearSearch . "'>";
                                    echo "<input type='submit' class='linkButton' name='f3_referenceId' value='" . $row['Reference'] . "'>";
                                    echo "</td></form>";
                                    echo "</tr>";
                                }
                            } catch(PDOException $e) {
                                echo "FAILURE!";
                                echo $e->GetMessage();
                            }
                            echo "</table>";
                        } else if (isset($f3_referenceId)) {
                            try {
                                $query =
"
select 
	Wishlist.Id as 'Reference',
    concat(Child.FirstName, ' ', Child.LastName) as 'Recipient',
	concat(Region.CountryName, ' (', Region.Code, ')') as 'TargetRegion',
    Wishlist.Year as 'Year',
    
    (case
        when Wishlist.YearDelivered is null then (case 
            when Wishlist.Admitted = 0 then '5Denied'
            else  (case
                when Wishlist.Year < year(now()) then (case
                    when Wishlist.Admitted = 1 then '1LateAdmitted'
                    else '0LateNoReview'
                end)
                else (case
                    when Wishlist.Admitted = 1 then '3Admitted'
                    else '2NoReview'
                end)
            end)
        end)
        else '4Delivered'
    end) as Status,
    
    wd.Text as 'WishText'
    
from Wishlist
	left join Region on Region.Code = Wishlist.RegionCode
	left join WishlistDescription as wd on wd.Id = Wishlist.Id and wd.Year = Wishlist.Year
	left join Child on Child.Id = Wishlist.Id
    
where Wishlist.Year = '" . $f3_yearSearch . "' and Wishlist.Id = '" . $f3_referenceId . "'
limit 1;
";
                                $f3_deliveryInfo = $dbo->query($query)->fetch();
                                echo "<div id='deliveryInfoBox'><div id='test'>";
                                echo "<p>Reference Id: " . $f3_deliveryInfo['Reference'] . "</p>";
                                echo "<P>Recipient: " . $f3_deliveryInfo['Recipient'] . "</p>";
                                echo "<p>Region: " . $f3_deliveryInfo['TargetRegion'] . "</p>";
                                echo "<br>";
                                echo "<p><a href='#' class='link' onclick='dlWish(`".$f3_deliveryInfo['Reference']."`,`".$f3_yearSearch."`)'>>> Download wish <<</a></p>";
                                echo "<br>";
                                echo "<p>Status</p>";
                                echo "<div id='statusDiv'><span id='coloredBox' class='status".$f3_deliveryInfo['Status']."'></span><p>" . getStatusString($f3_deliveryInfo['Status']) . "</p>";
                                echo "</div>";
                                echo "<br>";
                                echo "<p><a class='link' href='#' onclick='document.getElementById(`f3_search`).click()'>Close</a></p>";
                                echo "</div>";
                                if ($f3_deliveryInfo['Status'] === "0LateNoReview" || $f3_deliveryInfo['Status'] === "2NoReview") { // missing review
                                    echo "<div id='performAdmission'>";
                                    echo "<p>ATTENTION!<br>THESE CHANGED ARE PERMANENT!</p>";
                                    echo "<br><br>";
                                    echo "<form onSubmit='return confirm(`Are you sure you want to perform this action?`);' action='index.php' method='POST'>";
                                    echo "<input type='hidden' name='f3_yearSearch' value='" . $f3_yearSearch . "'>";
                                    echo "<input type='hidden' name='f3_referenceId' value='" . $f3_referenceId . "'>";
                                    echo "<div class='makeCentered'><input id='admitButton' type='submit' name='f3_admitWish' value='ADMIT'></div>";
                                    echo "<div class='makeCentered'><input id='denyButton' type='submit' name='f3_denyWish' value='DENY'></div>";
                                    echo "</form></div>";
                                }
                                echo "</div>";
                            } catch (PDOException $e) {
                                echo "FAILURE!";
                                echo $e->GetMessage();
                            }
                        }
                    ?>
                </div> <!-- #form3 -->
                <div id='form4' class='submission'>
                    <h2>Parcel Assigner</h2>
                    <div>
                        <?php
                            $query =
"
select
    Wishlist.Id as 'id', Wishlist.Year as 'year',
    (case
        when Wishlist.YearDelivered is null then (case 
            when Wishlist.Admitted = 0 then '5Denied'
            else  (case
                when Wishlist.Year < year(now()) then (case
                    when Wishlist.Admitted = 1 then '1LateAdmitted'
                    else '0LateNoReview'
                end)
                else (case
                    when Wishlist.Admitted = 1 then '3Admitted'
                    else '2NoReview'
                end)
            end)
        end)
        else '4Delivered'
    end) as status,
    Parcel.*
from Wishlist
	left join Parcel on Parcel.WishlistYear = Wishlist.Year and Parcel.WishlistId = Wishlist.Id
where Admitted = 1 and YearDelivered is null and Parcel.SleighNr is null
order by year;
";
                            try {
                                $result = $dbo->query($query);
                                if ($result->rowCount() === 0) {
                                    echo "Not enough admitted wishlists to create a new parcel!";
                                } else {
                                    echo "
                                    <form onSubmit='return confirm(`Do you wish to create this Parcel?`);' action='index.php' method='post'>
                                        <table class='listTable'>
                                    ";
                                    $i = 0;
                                    foreach ($result as $row) {
                                        echo "<tr><td><div class='horizontal_children'>";
                                        echo "<input type='checkbox' name='f4_checked[]' value='".$row['id']."Y".$row['year']."'> [".$row['year']."] ".$row['id'];
                                        if ($row['status'] === '1LateAdmitted') {
                                            echo " (LATE!!!)";
                                        }
                                        echo "</div></td></tr>";
                                        $i++;
                                    }
                                    echo "</table>";

                                    echo "
                                    <div class='inputField'>
                                    <br>
                                    <div class='makeCentered'><div>Assign to:</div></div>
                                        <div class='makeCentered'><select name='f4_sleighSelect' id='regionSelect'>
                                        <option value=''>--Choose a sleigh--</option>
                                    ";
                                    $query = 
"
select 
Sleigh.Nr as 'Number',
Sleigh.Name as 'Name',
Region.CountryName as 'Region'

from Sleigh
left join Region
    on Sleigh.RegionCode = Region.Code
";
                                    try {
                                        $result = $dbo->query($query);
                                        foreach ($result as $row) {
                                            if ($row['Number'] == $f4_sleighNumber) {
                                                echo '<option selected value="' . $row['Number'] . '">#' . $row['Number'] . ' - ' . $row['Name'] . ' (' . $row['Region'] . ')</option>';
                                            } else {
                                                echo '<option value="' . $row['Number'] . '">#' . $row['Number'] . ' - ' . $row['Name'] . ' (' . $row['Region'] . ')</option>';
                                            }
                                        }
                                    }  catch(PDOException $e) {
                                        echo "FAILURE!";
                                        echo $e->GetMessage();
                                    }
                                    echo "
                                    </select></div>
                                        </div> <!-- #inputField -->
                                        <br>
                                        <div class='makeCentered'>
                                            <input type='submit' name='f4_submit' value='Create Parcel'>
                                        </div> <!-- Centered item -->
                                    </form>";
                                }
                            } catch (PDOException $e) {
                                echo "FAILURE!";
                                echo $e->GetMessage();
                            }
                        ?>
                    </div>
                </div>
            </div> <!-- #content_right -->
        </div> <!-- #content -->
    </body>
</html>
