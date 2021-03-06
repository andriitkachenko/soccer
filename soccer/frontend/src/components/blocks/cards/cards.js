import classNames from 'classnames';
import './cards.css';

const Cards = ({red, yellow, amount}) => {
    const clsName = classNames('cards', {yellow : yellow, red : red});
    return(
        amount 
            ?   <div className={clsName}>
                   <span>{amount}</span>
                </div> 
            : null
    );
}

export default Cards;