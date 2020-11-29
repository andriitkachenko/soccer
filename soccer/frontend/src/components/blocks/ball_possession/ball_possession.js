import './ball_possession.css';

const BallPossession = ({all, half1, min}) => {
    if ((half1 || half1 === 0) && min > 45) {
        const t = min * all / 100.;
        const t2 = t - (45 * half1 / 100.);
        const p2 = Math.round(t2 * 100. / (min - 45));
        return (
            <div className="ball-possession">
                <span>{half1}</span>
                <span className='bold'>{p2}</span>
            </div>
        );
    }
    return(
        <div  className='bold'>{all}</div>
    );
}
export default BallPossession;