import json
import os
import requests


class NlitemeDataStructure:
    def __init__(self):
        pass
            
    def setParam(self, name, value):
        setattr(self, name, {"realname":name, "value":value})
    
    def to_JSON(self):
        return json.dumps(self, ensure_ascii=False, 
                          default=lambda o: {0: o.__dict__})


def upload(data_src, url, upload_type):
    files = None
    data = {'uploadtype': upload_type}
    try:
        print(f"Uploading {upload_type}: {data_src} to nliteme:{url}")
        if os.path.isfile(data_src):
            files = {'file': (os.path.basename(data_src), open(data_src, 'rb'))}
        else:
            data.update({'jsonstring': data_src})
        r = requests.Session().post(url, data=data, files=files, timeout=300 if files else 30)
        print("Client Headers: " + repr(r.request.headers))
        print("Server Headers: " + repr(r.headers))
        print("Status Code: {}".format(r.status_code))
        if r.status_code == 200:
            if r.headers['Content-Type'] == 'application/json':
                print(r.json())
            else:
                print(r.content)
        else:
            print("Error Content: {}".format(repr(r.content)))
    except Exception as e:
        print(f"Nliteme upload failed {repr(e)}")


class TestResultExample(NlitemeDataStructure):
    def __init__(self, name, set_default=True):
        super().__init__()
        self.setParam("filepath", name)
        if set_default:
            self._default_content()

    def _default_content(self):
        self.setParam("createdate", "2024-08-18 00:29:00")
        self.setParam("build", "Test_build2")
        self.setParam("increment", "Test_inc1")
        self.setParam("tsname", "Test_suite1")
        self.setParam("extracolumn_0", "Test_ue1")
        #self.setParam("extracolumn_1", 10)
        self.setParam("extracolumn_2", 944555)
        #self.setParam("extracolumn_3", 250)
        self.setParam("tcname", "TestCase000")
        self.setParam("tcverdict", "ok")
        self.setParam("description", "This is a test test result description")
        self.setParam("duration", 50)
        self.setParam("tlname", "Test_line5")
        self.setParam("fname", "616059")
        self.setParam("coverage", 25)
        self.setParam("flink", "http://localhost/feature/testfeature")


class TestResultDefectExample(TestResultExample):
    def __init__(self, name, defect):
        super().__init__(name=name, set_default=False)
        self.map_defect(defect=defect)
    
    def map_defect(self, defect):
        self.setParam("extracolumn_2", defect)
    

def main():
    jsonstring = TestResultExample('ftp://ftp.logs.server/test_result_path1').to_JSON()

    # uncomment to test the test result and defect mapping
    tr_mod =  TestResultDefectExample('ftp://ftp.logs.server/test_result_path1', 944555)
    #tr_mod.setParam("description", "This is a Very NEW test test result description")
    #jsonstring = tr_mod.to_JSON()

    # upload to db
    target_url = 'http://localhost/nliteme/upload.php';
    uploadtype = 'testresults'
    # print(jsonstring)
    
    upload(data_src=jsonstring, url=target_url, upload_type=uploadtype)

if __name__ == "__main__":
    main()