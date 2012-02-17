<?php 

$pdo = new PDO(
    'mysql:host=localhost;dbname=bankcrm',
    'root',
    ''
);

$lines = file('querybuilder2012-02-15.csv');

foreach ($lines as $line_num => $line) {
    //echo "Line #{$line_num} : " . $line . "\n";
    //list($tmp,$tmp,$tmp,$company_statut,$lastname,$firstname,$phone,$phone2,$gsm,$email,$ddn,$street_num,$zip,$city,$country,) =
   // 	split(';',$line);
    	
    list($tmp,$tmp,$tmp,$company_statut,$name,$street_num,$zip,$city,$country,$phone,$phone2,$gsm) = split(';',$line);
    		
	
	
    //	DATUM	ETTCTC	NUMCLI	TYPE	NOM	ADRESSE	PTTDMI	LOCDMI	PAYDMI	TELPRI	TELBUR	NUMMOB
    	
   //;;;Type;lastname;firstname;phone;phone2;gsm;email;datenaissance;street_num;zip;city;country
    	
    	
   $sql_account = "insert into account (name,company_statut,street_num,zip,city,country) values ";
   $sql_account .= " (\"".trim($name)."\",\"$company_statut\",\"$street_num\",\"$zip\",\"$city\",\"$country\"); \n";

   echo $sql_account;
   
   //$pdo->exec($sql_account) or die(print_r($pdo->errorInfo(), true));
   //$id_account = $pdo->lastInsertId();
   
   //echo $id_account;
   
   // change ddn format:
   //$ddn = substr($ddn, 0,4) . "-" . substr($ddn, 4,2) . "-" . substr($ddn, 6,2); 	

   //$sql_contact = "insert into contact (lastname,firstname,street_num,zip,city,country,phone,phone2,gsm,email,birthdate) values ";
   //$sql_contact .= " (\"$lastname\",\"$firstname\",\"$street_num\",\"$zip\",\"$city\",\"$country\",\"$phone\",\"$phone2\",\"$gsm\",\"$email\",\"$ddn\"); \n";
   
   //echo $sql_contact;
   
   //$pdo->exec($sql_contact) or die(print_r($pdo->errorInfo(), true));
   //echo $id_contact = $pdo->lastInsertId();
   
   //$sql_contact_account = "insert into accounts_contacts values ";
   //$sql_contact_account .= "($id_account,$id_contact )";
   
   //$pdo->exec($sql_contact_account) or die(print_r($pdo->errorInfo(), true));
   
   
   
}




?>