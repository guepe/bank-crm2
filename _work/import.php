<?php 

//$pdo = new PDO(
    'mysql:host=localhost;dbname=bankcrm',
    'root',
    ''
);

$lines = file('both.csv');


foreach ($lines as $line_num => $line) {
    echo "Line #{$line_num} : " . $line . "\n";
    list($tmp,$tmp,$tmp,$company_statut,$name,$street_num,$zip,$city,$country,$phone,$phone2,$gsm) =
    	split(';',$line);
    	
   $sql_account = "insert into account (name,company_statut,street_num,zip,city,country) values ";
   $sql_account .= " (\"$name\",\"$company_statut\",\"$street_num\",\"$zip\",\"$city\",\"$country\") \n";

   echo $sql_account;
   
   $pdo->exec($sql_account) or die(print_r($pdo->errorInfo(), true));
   $id_account = $pdo->lastInsertId();
   
   //echo $id_account;
   
   $sql_contact = "insert into contact (lastname,street_num,zip,city,country,phone,phone2,gsm) values ";
   $sql_contact .= " (\"$name\",\"$street_num\",\"$zip\",\"$city\",\"$country\",\"$phone\",\"$phone2\",\"$gsm\") \n";
   
   //echo $sql_contact;
   
   
   $pdo->exec($sql_contact) or die(print_r($pdo->errorInfo(), true));
   echo $id_contact = $pdo->lastInsertId();
   
   $sql_contact_account = "insert into accounts_contacts values ";
   $sql_contact_account .= "($id_account,$id_contact )";
   
   $pdo->exec($sql_contact_account) or die(print_r($pdo->errorInfo(), true));
   
   
   
}




?>