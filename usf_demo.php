<html>
    <head>
    <title>Test</title>
	
	<style>

		summary {color:blue;}

		
	</style>

    </head>
    <body>
	
	
        <?php
		
		/****************************************************************************************/
		/* demo of PHP based dashboard																*/
		/*                                                                                      
		
		In this demo the php drives the generated page - not a true MVC.
		
		1) Used to show basic elements of a dashboard: logo, summary section, detail section,
		   form submit, sorting via hfref's
		
		2) This demo uses a XAMPP stack with mySql and php files driving the backend.
		
		3) Gets are used so the arguments being passed are visible.
		
		4) location of this file: C:\xampp\htdocs\usf_demo.php
		
		5) The create_candidates.sql file can be used to recreate the candidates table in mySql.
		
		*/
		
		
		/****************************************************************************************/
	
	
		// -- code starts here --
		
		
		
			// get parameters
			if(isset($_GET['state'])) {
				$state = $_GET["state"];
			} else {
				$state = "all";
			}
		
			// open db
			$conn = open_db();
			
			// display logo
			logo("a");
		
			// this is the summary section
			summary($conn, $state);
			
			// formstting
			echo "<br>";
			hline();
	
			
			// the detail section
			detail($conn, $state);
			hline();
			hline();
		
			// close out
			close_db($conn);

        ?>
    </body>
</html>

<?php


// -------------  functions --------------------

function logo($var)
{
	// left, right logos and ctr txt

	echo '
		<table  style="width:100%" border="0" bordercolor="red" align="center" >
			<tr>
				<td><a href="https://www.usf.edu/">
				<img src="img/usf.png" alt="" border=0 height=100 width=100></img></td>
				<td >   <font size="10">';
	dash_name();
	echo '</font></td>
					
				<td><a href="https://en.wikipedia.org/wiki/Old_Glory">
				<img src="img/flag.jpg" alt=""  height=100 width=100></img></td>
			</tr>
		</table> ';

	// seperator

	hline();
	
	// add system status
	
	echo '
		<table bgcolor="#66ff99" style="width:100%">
			<tr>
			<td>';
	system_status();
	
	// and login status 
	
	echo '
			</td>
			<td></td>
			<td>';
	login_status();
	echo '
			</td>
			</tr>
		</table>'; 
	hline();
}

function summary($conn, $state)
{

	// construct the state selector
	// label 
	echo '
		<table bgcolor="#ffcc66" style="width:100%" >
			<tr>
			<td align="left">',"<b>State selection</b>",'</td>
			</tr>
		</table>';
  
	// current selection
	echo '
		<table bgcolor="#ffff99" style="width:100%">
			<tr>
			<td>',"Current State ", '<br> <b>',$state, '</b></td>';
	echo '<td align="right">', "Select new state or continue with current ";
	

	// the form
	echo '		
		<form action="/usf_demo.php" id="state">
	
		<select name="state" form="state">
			<option value="texas">Texas</option>
			<option value="florida">Florida</option>
			<option value="all">All</option>
		</select>
		<input type="submit">
		</form>
	';
			
	echo '</td></tr></table>';
	
	hline();
	
	// define where clause
	
	$DATE_CLAUSE = " AND date_received != '0000-00-00' ";
	
	if ($state == "all" )
	{
		$STATE_CLAUSE = "  ";
	}else{
		$STATE_CLAUSE = " AND STATE ='$state'  ";
	}
	
	// define sql
	$sql = "SELECT count(*) as COUNT FROM `candidates` as a 
	WHERE 1  $STATE_CLAUSE $DATE_CLAUSE ";

	$recv = run_sql($conn, $sql);
	
	$DATE_CLAUSE = " AND date_screened != '0000-00-00' ";
	$sql = "SELECT count(*) as COUNT FROM `candidates` as a 
	WHERE 1 $STATE_CLAUSE $DATE_CLAUSE";

	$scrn = run_sql($conn, $sql);
	
	$DATE_CLAUSE = " AND date_submitted != '0000-00-00' ";
	$sql = "SELECT count(*) as COUNT FROM `candidates` as a 
	WHERE 1  $STATE_CLAUSE $DATE_CLAUSE";

	$sub = run_sql($conn, $sql);
			
	echo '
		<div style="background-color:ffffcc">
		<table style="width:70%">
			<tr><td><b>Candidates Summary </b></td></tr>
			<th align="left">State</th>
			<th align="left">Received</th>
			<th align="left">Screened</th>
			<th align="left">Submitted</th>
	
			<tr>
			<td>', $state, '</td>
			<td>', $recv, '</td>
			<td>', $scrn, '</td>
			<td>', $sub, '</td>
		</tr>
		</table>
		</div';  	
}

function detail($conn, $state )
{

	// get the sort order if any
	if(isset($_GET['order'])) {
		$order = $_GET["order"];
	} else {
		$order = "desc";
	}
	// swap it to allow asc / desc sort
	if ($order == "asc") $order="desc";
	else $order = "asc";
	

	// get the sort col if any
	if(isset($_GET['sort'])) {
		$sort = $_GET["sort"];
	} else {
		$sort = "name";
	}

	$state_arg="<a href=usf_demo.php?state=$state&&order=$order&&sort=state>State</a> ";
	$name_arg="<a href=usf_demo.php?state=$state&&order=$order&&sort=name>Name</a> ";
	$recv_arg="<a href=usf_demo.php?state=$state&&order=$order&&sort=date_received>Dt Received</a> ";
	$scrn_arg ="<a href=usf_demo.php?state=$state&&order=$order&&sort=date_screened>Dt Screened</a>";
	$sub_arg ="<a href=usf_demo.php?state=$state&&order=$order&&sort=date_submitted>Dt Submitted</a>";

	echo '
		<div style="background-color:ffffcc">
		<table style="width:70%">
		<tr><td><b>Detailed Report</b></td></tr>
			<th align="left">', $state_arg,'</th>
			<th align="left">', $name_arg,'</th>
			<th align="left">', $recv_arg,'</th>
			<th align="left">', $scrn_arg,'</th>
			<th align="left">',$sub_arg,'</th>
	';

	$data = detail_stats($conn, $state, $sort, $order);

	$i = 0;
	
	while ( $i++ < $data[0][0] ){
		$name = $data['name'][$i] ;
		$type =  $data['type'][$i];
		$state = $data['state'][$i];
		$date_received =  $data['date_received'][$i];
		$date_screened = $data['date_screened'][$i];
		$date_submitted = $data['date_submitted'][$i];

		echo '

		  <tr>
			<td>', $state, '</td>
			<td>', $name, '</td>
			<td>', $date_received, '</td>
			<td>', $date_screened, '</td>
			<td>', $date_submitted, '</td>
		  </tr> ';
	} 

	echo '
		</table>
		</div';  

}


// draw a horizontal line 
function hline()
{
	echo '	
	<hr {
	  display: block;
	  margin-top: 0.5em;
	  margin-bottom: 0.5em;
	  margin-left: auto;
	  margin-right: auto;
	  border-style: inset;
	  border-width: 1px;
	} >';
}

// return system status
function dash_name()
{
	echo 'Recruitment Dash';
}


// return system status
function system_status()
{
	echo 'All systems updated 8:00am 9/10/2019';
}

// return login status 
function login_status()
{
	echo 'Login: Guru';
}

function run_sql($conn, $sql)
{
	
	// query
	$result = $conn->query($sql);
	
	if ($result->num_rows > 0) {
		// output data of each row
		$row = $result->fetch_assoc();
		return $row["COUNT"];
		
	} else  {
		echo "0 results";
		return null;
	}
}

function detail_stats($conn, $state, $sort, $order)
{

	if ($state == "all" )
	{
		$STATE_CLAUSE = "  ";
	}else{
		$STATE_CLAUSE = " AND STATE ='$state'  ";
	}
	
	// define sql
	$sql = "SELECT state, name, type, date_received, date_screened, date_submitted 
	FROM candidates  WHERE 1 $STATE_CLAUSE ORDER BY $sort $order ";

	// query
	$result = $conn->query($sql);

	$i = 0;
	
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			$i++;

			$data['name'][$i] = $row["name"];
			$data['state'][$i] = $row["state"];
			$data['type'][$i] = $row["type"];
			$data['date_received'][$i] = $row["date_received"];
			$data['date_screened'][$i] = $row["date_screened"];
			$data['date_submitted'][$i] = $row["date_submitted"];
		}
	$data[0][0] = $i;
	}
	 else  {
		$data[0][0] = 0;
	}

	return $data;
}

function open_db()
{
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "demo";
	$data = array();

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	return $conn;
}


function close_db($conn)
{
	$conn->close();
}


?>