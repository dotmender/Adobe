# CORE Order Status module

## Description
    - Login / Reister using mobule number OTP

Following scenarios cover for the login and reigistration :

1. Customer Login using mobile number OTP
2. Customer Registration using mobile number OTP

### Change log
* 1.0.0 Initial Version

### GraphQL API docs:

Below is the order status update REST API

##### GraphQL : Send OTP to Mobile Number
Sample Request :
```
{
    mutation {
      SendMobileOtp (
        input: {
            mobile_number: "1234567890"
            event: "login"
        }
      ){
             otp_reference_code
           message_code
           message
      }
    }
}
```
Sample Response :

On success:
```
{
    {
      "data": {
        "SendMobileOtp": {
          "otp_reference_code": "43",
          "message_code": "200",
          "message": "OTP Sent successfully"
        }
      }
    }
}
```

On failure: Can not send OTP
``` 
{
    {
      "errors": [
        {
          "message": "Something went wrong",
          "extensions": {
            "category": "graphql-input"
          },
          "locations": [
            {
              "line": 2,
              "column": 3
            }
          ],
          "path": [
            "SendOtp"
          ]
        }
      ],
      "data": {
        "SendOtp": null
      }
    }
}
```

##### GraphQL : Validate OTP
Sample Request :
```
{
    mutation {
      ValidateMobileOtp (
        input: {
            mobile_number: "98981091000"
            mobile_otp: "123456"
            otp_reference_code: "78"
          browser_data: [
            {
              ip: "198.192.0.1"
              region: "Thailand"
              bu_name: "Central"
              browser: "Chrome 103 on Windows 10"
              agent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36"
              timezone: "(GMT+5:30)"
              datetime: "Friday, 5 August 2022 (IST)"
              latitude: "21.7350314"
              longitude: "72.1299852"
            }
          ]
        }
      ){
             token
           message_code
           message
             cookies_data {
                store_id
            customer_id
            customer_group
            customer_token
            firstname
            lastname
            }        
      }
    }
}
```
Sample Response :

On success:
```
{
    {
      "data": {
        "ValidateMobileOtp": {
          "token": "eyJraWQiOiIxIiwiYWxnIjoiSFMyNTYifQ.eyJ1aWQiOjI2LCJ1dHlwaWQiOjMsImlhdCI6MTY2MDA1NDczMSwiZXhwIjoxNjYwMDU4MzMxfQ.ATxxcoXRuIhhnh-VuygoqScgcdWracrcG4sHdQQWagg",
          "message_code": "200",
          "message": "OTP have been verified successfully",
          "cookies_data": {
            "store_id": 1,
            "customer_id": "12345",
            "customer_group": "3",
            "customer_token": "faghfd",
            "firstname": "Rajesh",
            "lastname": "Rathod"
          }
        }
      }
    }
}
```

On failure: 
Case 1 - Wrong OTP
``` 
{
    {
      "errors": [
        {
          "message": "OTP entered is wrong",
          "extensions": {
            "category": "graphql-input"
          },
          "locations": [
            {
              "line": 2,
              "column": 3
            }
          ],
          "path": [
            "ValidateMobileOtp"
          ]
        }
      ],
      "data": {
        "ValidateMobileOtp": null
      }
    }
}
```

On failure: 
Case 2 - OTP is expired
``` 
{
    {
      "errors": [
        {
          "message": "OTP is expired",
          "extensions": {
            "category": "graphql-input"
          },
          "locations": [
            {
              "line": 2,
              "column": 3
            }
          ],
          "path": [
            "ValidateMobileOtp"
          ]
        }
      ],
      "data": {
        "ValidateMobileOtp": null
      }
    }
}
```

##### GraphQL : Customer Registration Using Mobile Number (If Mobile Number is not available in MDC)
Sample Request :
```
{
    mutation {
      createCustomerAccount (
        input: {
            mobile_number: "98981091000"
          fullname: "Rajesh Rathod"
          browser_data: [
            {
              ip: "198.192.0.1"
              region: "Thailand"
              bu_name: "Central"
              browser: "Chrome 103 on Windows 10"
              agent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36"
              timezone: "(GMT+5:30)"
              datetime: "Friday, 5 August 2022 (IST)"
              latitude: "21.7350314"
              longitude: "72.1299852"
            }
          ]
          is_subscribed: 1
          consent_privacy_version: "1.1"
          consent_marketing_status: true
          consent_privacy_status: true      
        }
      ){
             token
           message_code
           message
             cookies_data {
                store_id
            customer_id
            customer_group
            customer_token
            firstname
            lastname
            }        
      }
    }
}
```
Sample Response :

On success:
```
{
    {
      "data": {
        "createCustomerAccount": {
          "token": "eyJraWQiOiIxIiwiYWxnIjoiSFMyNTYifQ.eyJ1aWQiOjQzLCJ1dHlwaWQiOjMsImlhdCI6MTY2MDEyMTIxMSwiZXhwIjoxNjYwMTI0ODExfQ.ODDftOAGSlQZAMXl4Exy_i2SpazcvBif2gj0UB2gFOA",
          "message_code": "200",
          "message": "Customer registered successfully",
          "cookies_data": {
            "store_id": 1,
            "customer_id": "12345",
            "customer_group": "3",
            "customer_token": "faghfd",
            "firstname": "Rajesh",
            "lastname": "Rathod"
          }
        }
      }
    }
}
```

On failure: 
Case 1 - If Mobile Number is already exist
``` 
{
    {
      "errors": [
        {
          "message": "Mobile number is already exist, please login using OTP",
          "extensions": {
            "category": "graphql-input"
          },
          "locations": [
            {
              "line": 2,
              "column": 3
            }
          ],
          "path": [
            "createCustomerAccount"
          ]
        }
      ],
      "data": {
        "createCustomerAccount": null
      }
    }
}
```

Case 2 - If something went wrong or exception
``` 
{
    {
      "errors": [
        {
          "message": "Something went wrong",
          "extensions": {
            "category": "graphql-input"
          },
          "locations": [
            {
              "line": 2,
              "column": 3
            }
          ],
          "path": [
            "createCustomerAccount"
          ]
        }
      ],
      "data": {
        "createCustomerAccount": null
      }
    }
}
```