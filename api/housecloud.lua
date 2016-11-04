--[[
Created by GreenGene

Version: 1.0.5 BETA
--]]

os.loadAPI( "json" )

local function httpGET( URL )
    local returnValue = http.get( URL )

    if returnValue ~= nil then
        return returnValue.readAll( )
    else
        return false
    end
end

local enc = textutils.urlEncode

function makeAccount( username, password )
    local data = httpGET( "http://legologs.com/HOUSECLOUD?action=makeAccount&username="..enc( username ).."&password="..enc( password ) )

    if data ~= false then
        return true
    else
        return false
    end
end

function uploadFile( username, password, code, filename )
    local data = httpGET( "http://legologs.com/HOUSECLOUD?action=uploadFile&username="..enc( username ).."&password="..enc( password ).."&filename="..enc( filename ).."&code="..enc( code ) )

    if data ~= false then
        local dataJson = json.decode( data )
        if dataJson.error == false then
            return true
        else
            return false, dataJson.value
        end
    end
end

function getFileContents( username, password, filename )
    local data = httpGET( "http://legologs.com/HOUSECLOUD?action=getFileContents&username="..enc( username ).."&password="..enc( password ).."&filename="..enc( filename ) )

    if data ~= false then
        if data == "HOUSECLOUD_FILE_OPR_ERR" or data == "HOUSECLOUD_BAD_PASSCODE" then
            return false, data
        else
            return data
        end
    end
end

function getFiles( username, password )
    local data = httpGET( "http://legologs.com/HOUSECLOUD?action=getStoredFiles&username="..enc( username ).."&password="..enc( password ) )

    if data ~= false then
        if data ~= "HOUSECLOUD_BAD_PASSCODE" and data ~= "HOUSECLOUD_ACCOUNT_NOT_EXISTS"  then
            return data
        else
            return false, data
        end
    end
end

function deleteFile( username, password, filename )
    local data = httpGET( "http://legologs.com/HOUSECLOUD?action=deleteFile&username="..enc( username ).."&password="..enc( password ).."&filename="..enc( filename ) )

    if data ~= false then
        local dataJson = json.decode( data )
        if dataJson.error == false then
            return true
        else
            return false, dataJson.value
        end
    end
end

function verifyPassword( username, password )
    local data = httpGET( "http://legologs.com/HOUSECLOUD?action=verifyPassword&username="..enc( username ).."&password="..enc( password ) )

    if data ~= false then
        local dataJson = json.decode( data )
        if dataJson.error == false then
            return dataJson.value
        else
            return false, dataJson.value
        end
    end
end
