import './scores.css';

const Scores = ({all, half1}) => {
    if (half1 || half1 === 0) {
        return (
            <div className='scores'>
                {all}
                <span className="half">{half1}</span>
                <span className="half">{all - half1}</span>
            </div>
        );
    }
    return (
        <div className='scores'>{all}</div>
    );
}

export default Scores;