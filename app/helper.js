class Helper {
    static capitlizeFirstWordsOfString = (sentence) => {
        return sentence.replace(/(^\w{1})|(\s+\w{1})/g, letter => letter.toUpperCase());
    }

    static getQueryParams = () => {
        return new URL(window.location);
    }

    static objectIsEmpty = (incomingObject) => {
        return Object.keys(incomingObject).length === 0;
    }

    static getAllElements = (elementName) => {
        return document.querySelectorAll(elementName)
    }
}