<?php

?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sher="http://sherpa.sherpaan.nl/">
   <soapenv:Header/>
   <soapenv:Body>
      <sher:PaymentMethodList>
         <sher:securityCode><?php echo $this->securityCode; ?></sher:securityCode>
      </sher:PaymentMethodList>
   </soapenv:Body>
</soapenv:Envelope>