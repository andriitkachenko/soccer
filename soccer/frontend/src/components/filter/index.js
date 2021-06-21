const NO_FILTER = 0;
const FILTER_FAVORITE = 1;
const FILTER_15MIN = 2;
const FILTER_HALFTIME = 3; 
const FILTER_PREDICTABLE = 4; 

const DEFAULT_FILTER = NO_FILTER;

const getFilterTitle = (filter) => {
    let title = '';
    switch(filter) {
        case NO_FILTER : 
            title = 'All';
            break;
        case FILTER_FAVORITE : 
            title = 'Favorite';
            break;
        case FILTER_15MIN : 
            title = 'Half Ending';
            break;
        case FILTER_HALFTIME : 
            title = 'Halftime';
            break;
        case FILTER_PREDICTABLE : 
            title = 'Predictable';
            break;
        default : break;
    }
    return title;
}

const getEmptyFilteredListText = (filter) => {
    let text;
    switch(filter) {
        case NO_FILTER : 
            text = "Sorry, no game with live stats is available at the moment.";
            break;
        case FILTER_PREDICTABLE : 
            text = "No game we can consider predictable in current half. Please try to change filter or reset"; 
            break;
        case FILTER_15MIN : 
            text = "No game is within the last 15 minutes of current half. Please try to change filter or reset"; 
            break;
        case FILTER_HALFTIME : 
            text = "No game is at halftime. Please try to change filter or reset";
            break;
        case FILTER_FAVORITE : 
            text = "No game selected as favorite. Please try to change filter or reset";
            break;
        default : 
            text = "";
            break;
    }
    return text;
}

export {
    DEFAULT_FILTER,
    NO_FILTER,
    FILTER_FAVORITE,
    FILTER_15MIN,
    FILTER_HALFTIME,
    FILTER_PREDICTABLE,
    getFilterTitle,
    getEmptyFilteredListText
}