<?php

?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sher="http://sherpa.sherpaan.nl/">
   <soapenv:Header/>
   <soapenv:Body>
      <sher:ChangedItemSuppliersWithDefaults>
         <!--Optional:-->
         <sher:securityCode><?php echo $this->securityCode; ?></sher:securityCode>
         <sher:token><?php echo $this->counter; ?></sher:token>
         <sher:maxResult><?php echo $this->maxResult; ?></sher:maxResult>
      </sher:ChangedItemSuppliersWithDefaults>
   </soapenv:Body>
</soapenv:Envelope>