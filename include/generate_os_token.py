from keystoneauth1 import session, exceptions
from keystoneauth1.identity import v3
import sys

def generate_token(password, username, project_id):
    keystone_auth = v3.oidc.OidcPassword(auth_url="https://castor.cscs.ch:13000/v3",
                                          identity_provider="cscskc",
                                          protocol="openid",
                                          password=password,
                                          username=username,
                                          project_id=project_id,
                                          client_id="castor",
                                          client_secret="c6cc606a-5ae4-4e3e-8a19-753ad265f521",
                                          discovery_endpoint="https://auth.cscs.ch/auth/realms/cscs/.well-known/openid-configuration")
    sess = session.Session(auth=keystone_auth)
    token = sess.get_token()
    return token

if __name__ == "__main__":
    print(generate_token(sys.argv[1:][0], sys.argv[1:][1], sys.argv[1:][2]))
    sys.exit(0)