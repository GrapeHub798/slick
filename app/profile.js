const saveButtonId = "#saveButton"
const cancelButtonId = "#cancelButton"
const deleteButtonId = "#deleteButton"

let deleteButton

const inputElementName = "input"
const selectElementName = "select"
const error_id = "error_"
const error_prefix = `#${error_id}`

const loadingOverlay = '#loading-overlay'
const errorOverlay = '#error-overlay'
const hiddenClass = 'hidden'
const apiErrorId = '#api-error-message'

const saveUserEndpoint = "http://localhost/slicktext/server/index.php/user/create"
const getUserEndpoint = "http://localhost/slicktext/server/index.php/user/getOne"
const deleteUserEndpoint = "http://localhost/slicktext/server/index.php/user/delete"
const isExistingUser = false;

let loadingOverlayElement
let errorOverlayElement
let errorApiMessageElement

const clearForm = () => {
    const inputs = Helper.getAllElements(inputElementName)
    const selects = Helper.getAllElements(selectElementName)

    const combinedInputs = [...inputs, ...selects];
    combinedInputs.forEach(node => {
        node.value = "";
    })
}

const getFormData = () => {
    const inputs = Helper.getAllElements(inputElementName)
    const selects = Helper.getAllElements(selectElementName)

    const combinedInputs = [...inputs, ...selects];

    const data = {}

    combinedInputs.forEach(node => {
        const textBoxId = node.id;
        data[textBoxId] = node.value
    })
    return data;
}

const saveForm = () => {
    //get all the inputs
    const inputs = Helper.getAllElements(inputElementName)
    const selects = Helper.getAllElements(selectElementName)

    const combinedInputs = [...inputs, ...selects]

    let validInputs = 0;
    let formData = {};
    const requiredInputs = []

    combinedInputs.forEach(node => {
        if (node.classList.contains('required')) {
            requiredInputs.push(node)
        }
    })

    requiredInputs.forEach(singleInputElement => {
        validInputs += validateInput(singleInputElement)
    })

    const formIsValid = requiredInputs.length === validInputs
    if (formIsValid) {
        formData = getFormData()
    }

    return {
        data: formData,
        valid: formIsValid
    };
}

const validateInput = (inputElement) => {
    const inputValue = (inputElement.value)?.trim();

    if (!inputValue) {
        //get the name
        const textBoxId = inputElement.id;
        const errorElement = `${error_prefix}${textBoxId}`;
        const nameWithoutUnderscore = textBoxId.replace("_", " ");
        const cleanName = Helper.capitlizeFirstWordsOfString(nameWithoutUnderscore);
        displayError(errorElement, `${cleanName} is required`)
        return 0
    }
    return 1
}

const displayError = (element, errorMessage) => {
    const errorElement = document.querySelector(element);
    errorElement.innerText = errorMessage;
    errorElement.classList.remove(hiddenClass);
}

const hideErrors = () => {
    const errors = document.querySelectorAll(`div[id*='${error_id}']`);
    errors.forEach(singleError => {
        singleError.classList.add(hiddenClass)
    })
}

const saveUser = () => {
    hideErrors();
    const formData = saveForm();
    if (!formData.valid) {
        return;
    }

    fetch(saveUserEndpoint, {
        method: 'POST',
        headers: {
            Accept: 'application.json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
        .then(response => response.json())
        .then(res => {
            if (res.success){
                clearForm()
            }
            showApiMessage(res.message)
        })
        .catch(err => {
            showApiMessage('Unable to save user, contact support')
        })
}

const getExistingUser = async (existingUser) => {
    const fullUrl = `${getUserEndpoint}?userId=${existingUser}`
    const getUserResponse = await fetch(fullUrl)

    if (!getUserResponse.ok) {
        showApiMessage('Error getting User, contact support')
        return;
    }

    const resp = await getUserResponse.json()
    if (!resp?.id) {
        showApiMessage('Unable to find user');
        return;
    }
    return resp;
}

const showApiMessage = (message) => {
    errorApiMessageElement.innerText = message;
    errorOverlayElement.classList.remove(hiddenClass)

    //wait 5 second then hide
    setTimeout(() => {
        hideApiMessage()
    }, 5000)
}

const hideApiMessage = () => {
    errorOverlayElement.classList.add(hiddenClass)
}

const showLoading = () => {
    loadingOverlayElement.classList.remove(hiddenClass)
}

const hideLoading = () => {
    loadingOverlayElement.classList.add(hiddenClass)
}

const populateUserForm = (user) => {
    const inputs = Helper.getAllElements(inputElementName)
    const selects = Helper.getAllElements(selectElementName)

    inputs.forEach(singleInputElement => {
        const property = singleInputElement.id
        singleInputElement.value = user[property]
    })

    selects.forEach(singleSelectElemet => {
        const property = singleSelectElemet.id
        singleSelectElemet.value = user[property]
    })
}

const processExistingUser = async () => {

    const getParams = Helper.getQueryParams()
    const userId = getParams.searchParams.get('userId');
    if (!userId) {
        return;
    }
    //show loading
    showLoading()

    //get the user
    const existingUser = await getExistingUser(userId)
    if (!existingUser) {
        return;
    }
    populateUserForm(existingUser)
    deleteButton.classList.remove(hiddenClass)

    hideLoading()
}

const deleteUser = async () => {
    //get the user id
    const userIdElement = document.querySelector('input#id')
    const userId = userIdElement?.value
    if (!userId) {
        return;
    }

    const fullUrl = `${deleteUserEndpoint}?userId=${userId}`
    fetch(fullUrl, {
        method: 'DELETE',
        headers: {
            Accept: 'application.json',
            'Content-Type': 'application/json'
        },
    })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                clearForm();
            }
            showApiMessage(res.message)
        })
        .catch(err => {
            showApiMessage('Unable to save user, contact support')
        })

}

const setupForm = () => {
    const saveButton = document.querySelector(saveButtonId)
    saveButton.addEventListener("click", saveUser)

    const cancelButton = document.querySelector(cancelButtonId)
    cancelButton.addEventListener("click", clearForm)

    deleteButton = document.querySelector(deleteButtonId)
    deleteButton.addEventListener("click", deleteUser)

    deleteButton.classList.add(hiddenClass)

    loadingOverlayElement = document.querySelector(loadingOverlay)
    errorOverlayElement = document.querySelector(errorOverlay)
    errorApiMessageElement = document.querySelector(apiErrorId)

    hideErrors()
    hideLoading()
    hideApiMessage()
}

const init = () => {
    setupForm()
    processExistingUser()
}

init();