import os
import requests
#import pycurl
try:
    from cStringIO import StringIO
except ImportError:
    from io import BytesIO as StringIO

import json
import datetime
import random


class NlitemeDataStructure():
    def __init__(self):
        pass

    def setParam(self, name, value):
        setattr(self, name, {"realname":name, "value":value})

    def to_JSON(self, useIndentation=False):
        if(useIndentation):
            return json.dumps(self, ensure_ascii=False, default=lambda o: o.__dict__, sort_keys=True, indent=4)
        else:
            return json.dumps(self, ensure_ascii=False, default=lambda o: o.__dict__)


class TestResult(NlitemeDataStructure):
    def __init__(self):
        self.setBuild('')
        self.setBuildIncrement('')
        self.setDate('')
        self.setTestCase('')
        self.setTestSuite('')
        self.setVerdict('')
        self.setTestLine('')
        self.setUeType('')
        self.setExecutionTime('')
        self.setDescription('')
        self.setFilePath('')

    def setBuild(self, value):
        self.setParam('build', value)
    def setBuildIncrement(self, value):
        self.setParam('increment', value)
    def setDate(self, value):
        self.setParam('createdate', value)	
    def setTestCase(self, value):
        self.setParam('tcname', value)
    def setTestSuite(self, value):
        self.setParam('tsname', value)	
    def setVerdict(self, value):
        self.setParam('tcverdict', value)
    def setTestLine(self, value):
        self.setParam('tlname', value)		
    def setUeType(self, value):
        self.setParam('extracolumn_0', value)
    def setExecutionTime(self, value):
        self.setParam('duration', value)	
    def setDescription(self, value):
        self.setParam('description', value)		
    def setFilePath(self, value):
        self.setParam('filepath', value)

def generateResults(increment='30.00', ntrs=1000, nbuilds=10, nlines=4, days_offset=1):
    # generate some test results and add to dict
    testResults = []
    ueList = ['UE0', 'UE1', 'UE2']
    verdictList = ['ok', 'ok', 'ok', 'ok', 'ok', 'ok', 'failed', 'fatal', 'error', 'crash']
    testlineList = [f'Line_{i}' for i in range(nlines)]
    max_tc_per_suite = min(ntrs, 50)
    suite = [f'TSuite_{i}' for i in range(int(ntrs/max_tc_per_suite)+1)]
    max_tc_per_feature = min(ntrs, 20)
    features = [f"{i+1000} Feature_{i}" for i in range(int(ntrs/max_tc_per_feature)+1)]
    modulator = len(ueList) * len(verdictList) * len(testlineList)
    description = """THIS is a multiline 
    decrption with special characters like:
    ;'=-09\]~!
    '
    """

    for i in range(nbuilds):
        b_date = datetime.datetime.now() - datetime.timedelta(days=i+days_offset)
        b_date_str = b_date.strftime("%Y-%m-%d-%H-%M")
        tr_date = b_date + datetime.timedelta(hours=1)
        numOfTRs = ntrs
        while(numOfTRs):
            duration = random.randrange(30, 120)
            tcname = 'tc_00'+str(numOfTRs % (modulator * len(suite)))
            verdict_inx = random.randrange(0,len(verdictList))
            verdict = verdictList[verdict_inx]
            tr_date += datetime.timedelta(seconds=duration)
            tr = TestResult()
            tr.setBuild(f'{increment}_{b_date_str}')
            tr.setBuildIncrement(increment)
            tr.setTestSuite(suite[int(numOfTRs / max_tc_per_suite)])
            tr.setDate(tr_date.strftime("%Y-%m-%d %H:%M:%S"))
            tr.setTestCase(tcname)
            tr.setVerdict(verdict)
            tr.setTestLine(testlineList[numOfTRs % len(testlineList)])
            tr.setUeType(ueList[numOfTRs % len(ueList)])
            tr.setExecutionTime(duration)
            tr.setDescription(description)
            tr.setFilePath(f'ftp://ftp.logs.server/{tcname}_{tr_date.strftime("%Y%m%d_%H%M%S")}.7z')
            tr.setParam("fname", features[int(numOfTRs / max_tc_per_feature)])
            tr.setParam("coverage", 5)
            # optionally provide hiperlink to the test feature description on an external server
            tr.setParam("flink", f"http://localhost/feature/testfeature?id={1000+int(numOfTRs / max_tc_per_feature)}")
            if verdict != 'ok':
                tr.setParam("extracolumn_2", 1000 + verdict_inx*100)
            testResults.append(tr.to_JSON())
            numOfTRs -= 1
        
    # encode the list to json string
    #jsonstring = json.dumps(testResults, default=lambda o: o.__dict__)
    #print(jsonstring)
    
    with open('export.json', 'w') as fh:
        fh.write("\n".join(testResults))


#def uploadFileContent():
#    """Upload function using pycurl
#
#    """
#    # upload to db
#    target_url = 'http://localhost/nliteme/upload.php';
#    uploadtype = 'testresults'
#    post = 'export.json';
#    buf = StringIO()
#    try:
#        c = pycurl.Curl()
#        c.setopt(c.URL, target_url )
#        c.setopt(c.HTTPPOST, [('uploadtype', (c.FORM_CONTENTS, uploadtype)),("file", (c.FORM_FILE, post, c.FORM_CONTENTTYPE, "application/json"))])
#        c.setopt(c.CONNECTTIMEOUT, 1800)
#        c.setopt(c.TIMEOUT, 1800)
#        c.setopt(c.WRITEFUNCTION, buf.write)
#        c.perform()
#        print(buf.getvalue())
#    except:
#        print("Upload failed")
#    buf.close()


def upload(data_src, url, upload_type):
    """Upload function using reqyests module

    """
    files = None
    data = {'uploadtype': upload_type}
    try:
        print(f"Uploading {upload_type}: {data_src} to nliteme:{url}")
        if os.path.isfile(data_src):
            files = {'file': (os.path.basename(data_src), open(data_src, 'rb'), "application/json")}
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
        

def main():
    incs = ['10.00', '20.00', '30.00', '40.00']
    for i, inc in enumerate(incs):
        generateResults(increment=inc, ntrs=300, nbuilds=10, nlines=4, days_offset=10 - i)
        # upload to db
        # using requests
        target_url = 'http://localhost/nliteme/upload.php'
        uploadtype = 'testresults'
        upload(data_src='export.json', url=target_url, upload_type=uploadtype)

        #using pycurl
        #uploadFileContent()

if __name__ == "__main__":
    main()
