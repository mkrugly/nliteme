#!python
import os
import requests
import json
import sys, getopt
import re

class JsonObject:
    def to_JSON(self, useIndentation=False):
        if(useIndentation):
            return json.dumps(self, default=lambda o: o.__dict__, sort_keys=True, indent=4)
        else:
            return json.dumps(self, default=lambda o: o.__dict__)

class Build(JsonObject):
    def __init__(self):
        self.setBuild('')
        self.setIncrement('')
        self.setDate('')
        self.setDescription('')
        
    def setParam(self, name, value):
        setattr(self, name, {"realname":name,"value":value})
    def setIncrement(self, value):
        self.setParam('increment', value)
    def setBuild(self, value):
        self.setParam('build', value)
    def setDate(self, value):
        self.setParam('createdate', value)
    def setDescription(self, value):
        self.setParam('description', value)
        
    def getParam(self, name):
        return getattr(self, name)
    def getBuild(self):
        return self.getParam('build')
    def getIncrement(self):
        return self.getParam('increment')
    def getDate(self):
        return self.getParam('createdate')
    def getDescription(self):
        return self.getParam('description')

# temp function used to make sure the strings can be utf-8 encoded
def removeNonAscii(s): return "".join(i for i in s if ord(i)<128)
  
def main(argv):
    descriptionStr = ''
    buildname = ''
    changesetfile = ''
    baselinefile = ''
    buildtype = 'I'
    # parse cli args
    try:
        opts, args = getopt.getopt(argv,"hb:c:n:t:")
    except getopt.GetoptError:
        print(sys.argv[0] + ' -n <buildname> -c <changesetdifffile> -b <baselinefile> -t <buildtype>')
        sys.exit(2)
    for opt, arg in opts:
        if opt == '-h':
            print(sys.argv[0] + ' -n <buildname> -c <changesetdifffile> -b <baselinefile> -t <buildtype>')
            sys.exit()
        elif opt == '-b':
            baselinefile = arg
        elif opt == '-c':
            changesetfile = arg
        elif opt == '-n':
            buildname = arg
        elif opt == '-t':
            buildtype = arg
    
    # make sure the build name is given and has a right format
    buildNameMatch = re.match( r'^(\d{2}.\d{2})_(\d{4}-\d{2}-\d{2})-(\d{2}-\d{2})$', buildname)   
    if not buildNameMatch:
        print("Build name not specified")
        sys.exit(2)
    else:
        # prepare a build description
        if(changesetfile):
            with open (changesetfile, "r") as fh:
                descriptionStr = descriptionStr + removeNonAscii("".join(fh.readlines()))
        if(baselinefile):
            with open (baselinefile, "r") as fh:
                descriptionStr = descriptionStr + "\n====================================================================================\n"
                descriptionStr = descriptionStr + "Baselines List:\n--------------------\n" + " ".join(fh.readlines())
        descriptionStr = descriptionStr.strip()      
        # create json string for the upload
        buildObj = Build()
        buildObj.setDescription(descriptionStr)
        buildObj.setBuild(buildNameMatch.group())
        buildObj.setIncrement(buildNameMatch.group(1)+'_'+buildtype)
        buildObj.setDate(buildNameMatch.group(2)+' '+buildNameMatch.group(3).replace('-', ':'))
        jsonstring = '{"0":' + buildObj.to_JSON() + '}'

        # upload to db
        target_url = 'http://localhost/nliteme/upload.php';
        uploadtype = 'builds'
        data = {'uploadtype': uploadtype}
        files = None
        try:
            print(f"Uploading {uploadtype} as {jsonstring} to nliteme:{target_url}")
            if os.path.isfile(jsonstring):
                files = {'file': (os.path.basename(jsonstring), open(jsonstring, 'rb'))}
            else:
                data.update({'jsonstring': jsonstring})
            r = requests.Session().post(target_url, data=data, files=files, timeout=300 if files else 30)
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

if __name__ == "__main__":
   main(sys.argv[1:])