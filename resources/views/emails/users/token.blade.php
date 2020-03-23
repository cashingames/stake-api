<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <style>
        body{
            background-color: #fff;
            font-size: 20px;
            display: flex;
            justify-content: center;
            font-family: 'Times New Roman', Times, serif;
            text-align: justify;
        }
       
        
        .card {
            margin-top: 50px;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
            width: 350px;
        }
        
        h3 {
            color: white;
            display:flex;
            justify-content: center;
        }

        .heading{
            background-color: #4361f6;
            padding: 2px;
            border-radius: 2px;
        }

        .container{
            padding: 2px 16px;
            margin-top: 5px;
        }
        
    </style>
    
</head>
<body>
    <div class = "card">
        <div class= "heading">
            <h3> Recover Your Password </h3>    
        </div>
        <div class= "container">
            <span><b> Hello There !!</b></span>
            <p>You requested for a password reset!. 
            To reset your password, please use this code <b> {{$token}} .</b> </p>
            <span> If you did not request for a reset, please ignore this email.</span>
        </div>
    </div>
</body>
</html>


