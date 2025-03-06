import pycurl
try:
    from cStringIO import StringIO
except ImportError:
    from io import BytesIO as StringIO
 
buf = StringIO()
target_url = 'http://localhost/nliteme/upload.php';


############ Example on how to upload a results as json string
uploadtype = 'testresults'
jsonstring = """{"0":{
"filepath":{"realname":"filepath","value":"ftp:\/\/localhost\/logs\/DailyBuilds\/30.00_2013-11-27-01-03\/perlTest"}
,"createdate":{"realname":"createdate","value":"2017-03-11 02:02:00"}
,"build":{"realname":"build","value":"30.00_2013-11-27-01-03"}
,"increment":{"realname":"increment","value":"30.00"}
,"tsname":{"realname":"tsname","value":"MyTestSuite"}
,"extracolumn_0":{"realname":"extracolumn_0","value":"UE1"}
,"extracolumn_1":{"realname":"extracolumn_1","value":"1000"}
,"extracolumn_2":{"realname":"extracolumn_2","value":"1"}
,"extracolumn_3":{"realname":"extracolumn_3","value":"95.05"}
,"tcname":{"realname":"tcname","value":"perlTest"}
,"tcverdict":{"realname":"tcverdict","value":"ok"}
,"description":{"realname":"description","value":"Detailed description:"}
,"duration":{"realname":"duration","value":50}
,"tlname":{"realname":"tlname","value":"Line_1"}
}}"""

c = pycurl.Curl()
c.setopt(c.URL, target_url )
c.setopt(c.HTTPPOST, [('uploadtype', (c.FORM_CONTENTS, uploadtype)),('jsonstring', (c.FORM_CONTENTS, jsonstring, c.FORM_CONTENTTYPE, "application/json"))])
c.setopt(c.WRITEFUNCTION, buf.write)
#c.setopt(c.CONNECTTIMEOUT, 5)
#c.setopt(c.TIMEOUT, 8)
#c.setopt(c.PROXY, 'http://proxy')
c.perform()
print(buf.getvalue())
buf.close()
