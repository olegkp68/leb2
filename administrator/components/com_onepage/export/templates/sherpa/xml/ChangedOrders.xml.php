<?php

?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sher="http://sherpa.sherpaan.nl/">
   <soapenv:Header/>
   <soapenv:Body>
      <sher:ChangedOrders>
         <!--Optional:-->
         <sher:securityCode><?php echo $this->securityCode; ?></sher:securityCode>
         <sher:token><?php echo $this->order_token; ?></sher:token>
         <sher:count>99999</sher:count>
      </sher:ChangedOrders>
   </soapenv:Body>
</soapenv:Envelope>