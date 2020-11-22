import './shots.css';

const Shots = ({sgAll, sgHalf1, shAll, shHalf1, diff}) => {
    const sg = sgAll ? sgAll : 0;
    if (shHalf1 || shHalf1 === 0) {
        const sg1 = sgHalf1 ? sgHalf1 : 0;
        if (diff) {
            return (
                <div className="shots">{shAll - shHalf1}-{sg-sg1}</div>
            );
        } else {
            return (
                <div className="shots">
                    {shAll}-{sg}
                    <span>{shHalf1}-{sg1}</span>
                    <span  className='bold'>{shAll - shHalf1}-{sg - sg1}</span>
                </div>
            );
        }
    }
    return(
        <div  className='bold'>{shAll}-{sg}</div>
    );
}
export default Shots;