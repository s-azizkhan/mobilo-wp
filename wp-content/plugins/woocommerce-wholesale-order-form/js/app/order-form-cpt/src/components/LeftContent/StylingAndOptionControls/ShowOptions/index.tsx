import React from 'react';
import Placeholder from './Placeholder';
import ButtonText from './ButtonText';
import ProductsPerPage from './ProductsPerPage';
import Sort from './Sort';
import ColumnHeadingTitle from './ColumnHeadingTitle';
import SubmitOnEnter from './SubmitOnEnter';
import SubmitOnChange from './SubmitOnChange';
import ShowPopUp from './ShowPopUp';
import { getPropValue } from '../../../../helpers/getPropValue';
import { updateStyling } from '../../../../helpers/updateStyling';
// const getPropValue = (props: any) => {

//     const { styling, id, target, style, extra } = props;

//     if (extra) {
//         if (
//             typeof styling.styles !== 'undefined' &&
//             typeof styling.styles[id] !== 'undefined' &&
//             typeof styling.styles[id][target] !== 'undefined' &&
//             typeof styling.styles[id][target][style] !== 'undefined' &&
//             typeof styling.styles[id][target][style][extra] !== 'undefined'
//         )
//             return styling.styles[id][target][style][extra];
//         else
//             return null;
//     } else {
//         if (
//             typeof styling.styles !== 'undefined' &&
//             typeof styling.styles[id] !== 'undefined' &&
//             typeof styling.styles[id][target] !== 'undefined' &&
//             typeof styling.styles[id][target][style] !== 'undefined'
//         )
//             return styling.styles[id][target][style];
//         else
//             return null;
//     }

// }

// const updateStyling = (props: any) => {

//     const { setStyles, styling, id, target, toUpdate } = props;

//     if (typeof styling.styles[id] === 'undefined') {
//         const newData = {
//             [id]: {
//                 [target]: {
//                     ...toUpdate
//                 }
//             }
//         }

//         setStyles({
//             ...styling,
//             styles: {
//                 ...styling.styles,
//                 ...newData
//             }
//         });

//     } else {

//         setStyles({
//             ...styling,
//             styles: {
//                 ...styling.styles,
//                 [id]: {
//                     ...styling.styles[id],
//                     [target]: {
//                         ...styling.styles[id][target],
//                         ...toUpdate
//                     }
//                 }
//             }
//         });

//     }

// }

const ShowOptions = (props: any) => {

    const { styling, setStyles } = props;
    const id = styling.item.id;
    const styleProps = {
        styling, setStyles, id, updateStyling, getPropValue
    }

    const displayOptions = () => {

        if (styling.item.type === 'ROW') {

            return (<>No Options.</>);

        } else if (styling.item.type === 'ITEM') {

            let options = [];

            switch (styling.item.itemId) {
                // Header / Footer Elements
                case 'search-input':
                    options.push(
                        <SubmitOnEnter {...styleProps} target='props' />,
                        <Placeholder {...styleProps} target='props' />
                    );
                    break;
                case 'category-filter':
                    options.push(
                        <SubmitOnChange {...styleProps} target='props' />,
                        <Placeholder {...styleProps} target='props' />
                    );
                    break;
                case 'add-selected-to-cart-button':
                    options.push(
                        <ButtonText {...styleProps} target='props' />
                    );
                    break;
                case 'cart-subtotal':
                    break;
                case 'product-count':
                    break;
                case 'pagination':
                    options.push(
                        <ProductsPerPage {...styleProps} target='props' />
                    );
                    break;
                case 'search-button':
                    options.push(
                        <ButtonText {...styleProps} target='props' />
                    );
                    break;
                case 'clear-filters':
                    options.push(
                        <ButtonText {...styleProps} target='props' />
                    );
                    break;
                // Widget
                case 'attribute-filter':
                    break;
                case 'cart-widget':
                    break;
                case 'filter-products-by-attribute':
                    break;
                case 'filter-products-by-price':
                    options.push(
                        <Placeholder {...styleProps} target='props' />
                    );
                    break;
                // Table Elements
                case 'product-image':
                    options.push(
                        <ShowPopUp {...styleProps} target='props' />,
                        <ColumnHeadingTitle {...styleProps} target='props' />
                    );
                    break;
                case 'product-name':
                    options.push(
                        <ShowPopUp {...styleProps} target='props' />,
                        <Sort {...styleProps} target='props' />,
                        <ColumnHeadingTitle {...styleProps} target='props' />
                    );
                    break;
                case 'sku':
                    options.push(
                        <ColumnHeadingTitle {...styleProps} target='props' />
                    );
                    break;
                case 'in-stock-amount':
                    options.push(
                        <ColumnHeadingTitle {...styleProps} target='props' />
                    );
                    break;
                case 'price':
                    options.push(
                        <ColumnHeadingTitle {...styleProps} target='props' />
                    );
                    break;
                case 'quantity-input':
                    options.push(
                        <ColumnHeadingTitle {...styleProps} target='props' />
                    );
                    break;
                case 'add-to-cart-button':
                    options.push(
                        <ColumnHeadingTitle {...styleProps} target='props' />,
                        <ButtonText {...styleProps} target='props' />
                    );
                    break;
                case 'combo-variation-dropdown':
                    options.push(
                        <ColumnHeadingTitle {...styleProps} target='props' />
                    );
                    break;
                case 'standard-variation-dropdowns':
                    options.push(
                        <ColumnHeadingTitle {...styleProps} target='props' />
                    );
                    break;
                case 'product-meta':
                    options.push(
                        <ColumnHeadingTitle {...styleProps} target='props' />
                    );
                    break;
                case 'global-attribute':
                    options.push(
                        <ColumnHeadingTitle {...styleProps} target='props' />
                    );
                    break;
                case 'short-description':
                    options.push(
                        <ColumnHeadingTitle {...styleProps} target='props' />
                    );
                    break;
                case 'add-to-cart-checkbox':
                    // options.push(
                    // <ColumnHeadingTitle {...styleProps} target='props' />
                    // );
                    break;
                default:
                    options.push(<></>);

            }

            if (options.length > 0)
                return options.map((component: any, key: number) => React.cloneElement(component, { key }));
            else
                return (<>No Options.</>)

        }
    }

    return (
        <>
            {displayOptions()}
        </>
    )
}

export default ShowOptions;