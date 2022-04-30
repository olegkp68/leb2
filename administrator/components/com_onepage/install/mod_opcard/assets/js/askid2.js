function op_check1(useAlert)
{

 var mya = ['national_id_name', 'national_id', 'birth_date']; 
 ret = true; 
 for (var i = 0; i<mya.length; i++)
  {
    e = document.getElementById(mya[i]+'_field'); 
	if (e!=null)
	 {
	   if (e.value != '')
	     {
		   e2 = document.getElementById(mya[i]+'_div');
		   if (e2!=null)
		    e2.className = e2.className.split('missing').join(''); 
		
		 }
		else
		 {
		   e2 = document.getElementById(mya[i]+'_div');
		   if (e2!=null)
		    e2.className += ' missing';
		  
		   ret = false;
		 }
	 }
  }
  if (!ret) alert(op_general_error); 
  if (ret)
  {
  ee = document.getElementById('birth_date_field'); 
 
  if (ee != null)
   {
     var myDate = new Date(ee.value); 

	 var curdate = new Date(); 
	 var Z = curdate.getTime();
	 age = parseInt('18');
	 
	 var y = parseInt(curdate.getFullYear()); 
	 var dif = y - age;
	 curdate.setYear(dif); 
	
	 var nowM = curdate.getTime();
	 var tT = myDate.getTime();
	 if (tT <= nowM) 
	 {
	 ret=true; 
	 
	 }
	 else  
	  {
	    ee.className += ' missing';
		ret = false;
		alert(op_age_alert); 
	  }
	 
   }
  }
  
 
  return ret; 
 
}

function op_check2(useAlert)
{
 
 ret = true; 
 e = document.getElementById('over_18_field'); 
 
 	if (e!=null)
	 {
	   if ((e.checked != null ) && (e.checked == true))
	     {
		   e2 = document.getElementById('over_18_div');
		   if (e2!=null)
		    e2.className = e2.className.split('missing').join(''); 
		
		 }
		else
		 {
		   e2 = document.getElementById('over_18_div');
		   if (e2!=null)
		    e2.className += ' missing';
		   ret = false;
		  
		 }
	 }
	 
  
  if (!ret) alert(op_general_error); 
  return ret; 
 
}
function op_check3(useAlert)
{
 
 ret = true; 
 e = document.getElementById('over_21_field'); 
 
 	if (e!=null)
	 {
	   if ((e.checked != null ) && (e.checked == true))
	     {
		   e2 = document.getElementById('over_21_div');
		   if (e2!=null)
		    e2.className = e2.className.split('missing').join(''); 
		
		 }
		else
		 {
		   e2 = document.getElementById('over_21_div');
		   if (e2!=null)
		    e2.className += ' missing';
		   ret = false;
		  
		 }
	 }
	 
  
  if (!ret) alert(op_general_error); 
  return ret; 
 
}