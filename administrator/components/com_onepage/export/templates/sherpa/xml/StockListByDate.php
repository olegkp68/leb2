<?php


?><soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:sher="http://sherpa.sherpaan.nl/">
   <soapenv:Header/>
   <soapenv:Body>
      <sher:StockListByDate>
         <!--Optional:-->
         <sher:securityCode><?php echo $this->securityCode; ?></sher:securityCode>
         <sher:stockChangeDate>2018-01-01T00:00:01</sher:stockChangeDate>
         <sher:maxResult>99999</sher:maxResult>
      </sher:StockListByDate>
   </soapenv:Body>
</soapenv:Envelope>