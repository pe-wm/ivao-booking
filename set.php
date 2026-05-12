<?php
session_start();
function redirect($url)
{
	if (headers_sent())
		die("<script>window.location.href='$url';</script>");
	else
	{
		header("Location: $url");
		die();
	}
}

$_SESSION["LOGIN"] = new stdClass();
$_SESSION["LOGIN"]->firstname = "Peter";
$_SESSION["LOGIN"]->lastname = "Griffin";
//echo ("$_SESSION set");
redirect("check.php");