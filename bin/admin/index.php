<html>
<head>
    <script src='http://code.jquery.com/jquery-1.10.1.min.js'></script>
</head>
<body>
<a href="carlos_danger.php">Carlos Danger</a><br>
<a href="create_volley.php">Create a Volley for Team Volley</a><br>
<a href="manage_user.php">Edit a User</a><br>
<a href="edit_invite_messages.php">Edit Invite Messages</a><br>
<a href="manage_explore.php">Manage Explore Section</a><br>
<a href="wipe_user.php">Wipe a User</a><br>
<a href="edit_tags.php">Edit Tags</a><br>
<br><hr><br>
<?php 
require_once 'vendor/autoload.php';
$names = BIM_Config::getBootConfNames();
foreach( $names as $name ){
    echo "<a href='boot/edit/$name'>Edit Boot Conf $name</a><br>";
}

?>

<script type='text/javascript'>
function createNewBootConf(){
	var name = $("#bootName").val();
    location.href='boot/edit/' + name;
}
</script>
<input type='text' id='bootName'><input type='button' value='create new boot conf' onclick='createNewBootConf();'>
</body>
</html>