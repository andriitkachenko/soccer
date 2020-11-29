import './red_card.css';

const RedCard = ({host, guest, amount}) => {
    const clsName = 'red-card' + (host ? ' h' : '') + (guest ? ' g' : '');
    if (amount) {
        return(
            <div className={clsName}>{amount}</div>
        );
    }
    return null;
}

export default RedCard;