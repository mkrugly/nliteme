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

def main():
    tl = NlitemeDataStructure()
    tl.setParam("fname", "112233 Example test festure")
    tl.setParam("hlink", r'https://link_to_feature_in_the_testfeatures_external_server?id=112233')
    tl.setParam("description", """test it""")
    jsonstring = tl.to_JSON()

    # upload to db
    target_url = 'http://localhost/nliteme/upload.php';
    uploadtype = 'features'
    print(jsonstring)

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
    main()