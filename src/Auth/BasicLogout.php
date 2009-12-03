<html>
    <head>
    </head>
    <body>
<script>

function getHTTPObject() { 
    if (typeof XMLHttpRequest != 'undefined') { 
        return new XMLHttpRequest(); 
    } 
    try { 
        return new ActiveXObject("Msxml2.XMLHTTP"); 
    } catch (e) { 
        try { 
            return new ActiveXObject("Microsoft.XMLHTTP"); 
        } catch (e) {
        } 
    } 
    
    return false; 
}

var url = '_logout.php';

var http = getHTTPObject();
http.open("get", url, false, 'logout', 'logout' ); 
http.send(""); 
if (http.status == 200) { 
} else { 
    alert("Incorrect username and/or password."); 
} 

</script>

Logged out.<br>
<br>
<a href='../index.php'>Login</a>
    </body>
</html>
