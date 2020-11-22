import './red_card.css';

const RedCard = ({amount}) => {
    if (amount) {
        return(
            <div className='red-card'>{amount}</div>
        );
    }
    return null;
}

export default RedCard;