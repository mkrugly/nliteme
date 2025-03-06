import pycurl
try:
    from cStringIO import StringIO
except ImportError:
    from io import BytesIO as StringIO
import json
 
class NlitemeDataStructure:
    def __init__(self):
        pass        
    def setParam(self, name, value):
        setattr(self, name, {"realname":name, "value":value})
    def to_JSON(self):
        return json.dumps(self, ensure_ascii=False, 
                          default=lambda o: {0: o.__dict__})

def main():
    tl = NlitemeDataStructure()
    tl.setParam("tsname", "TSuite_1")
    tl.setParam("description", "This is a test suite description")
    jsonstring = tl.to_JSON()

    # upload to db
    target_url = 'http://localhost/nliteme/upload.php';
    uploadtype = 'testsuites'
    print(jsonstring)
    buf = StringIO()
    try:
        c = pycurl.Curl()
        c.setopt(c.URL, target_url )
        c.setopt(c.HTTPPOST, [('uploadtype', (c.FORM_CONTENTS, uploadtype)),('jsonstring', (c.FORM_CONTENTS, jsonstring, c.FORM_CONTENTTYPE, "application/json"))])
        c.setopt(c.WRITEFUNCTION, buf.write)
        c.perform()
        print(buf.getvalue())
    except:
        print("Upload failed")
    buf.close() 

if __name__ == "__main__":
    main()