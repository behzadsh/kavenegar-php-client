<?php

return [

    /*
     | Put your API key here or put it in your .env file under K_API_KEY. 
     */
    'api_key' => env('K_API_KEY', null),


    /*
     | Here you can set the sender number. If you set sender number here or in .env file,
     | all messages would send with the given number. For overwriting it on the run you
     | should pass the desired number as sender or line parameter.
     | Also you can leave it as null, and set the default sender number by account config
     | API.
     */
    'sender'  => env('K_SENDER_NUM', null),
    
];