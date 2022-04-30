<?php 

?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sher="http://sherpa.sherpaan.nl/">
   <soapenv:Header/>
   <soapenv:Body>
      <sher:OrderInfo>
         <!--Optional:-->
         <sher:securityCode><?php echo $this->securityCode; ?></sher:securityCode>
         <!--Optional:-->
         <sher:orderNumber><?php echo $this->orderNumber; ?></sher:orderNumber>
      </sher:OrderInfo>
   </soapenv:Body>
</soapenv:Envelope>