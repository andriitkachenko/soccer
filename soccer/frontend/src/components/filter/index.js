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
            text = "No live game with statistics at the moment";
            break;
        case FILTER_PREDICTABLE : 
            text = "No game is predictable enough (any team has 7 shots or 4 shots on goal) in current half"; 
            break;
        case FILTER_15MIN : 
            text = "No game is within last 15 minutes of current half"; 
            break;
        case FILTER_HALFTIME : 
            text = "No game is at halftime";
            break;
        case FILTER_FAVORITE : 
            text = "No game selected as favorite";
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