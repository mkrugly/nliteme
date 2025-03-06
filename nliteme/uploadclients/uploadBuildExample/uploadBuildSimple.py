import pycurl
try:
    from cStringIO import StringIO
except ImportError:
    from io import BytesIO as StringIO
 
buf = StringIO()
target_url = 'http://localhost/nliteme/upload.php'

############ Example on how to upload a results as json string
uploadtype = 'builds'
jsonstring = """{"0":{
"build":{"realname":"build","value":"TestBUILD222"}
,"increment":{"realname":"increment","value":"testINC222_I"}
,"description":{"realname":"description","value":"Detailed description:"}
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
