import React from 'react';

import Alignment from './Alignment';
import Margin from './Margin';
import Padding from './Padding';
import FontSize from './FontSize';
import Width from './Width';

const getStyleValue = (props: any) => {

    const { styling, id, target, style, extra } = props;

    if (extra) {
        if (
            typeof styling.styles !== 'undefined' &&
            typeof styling.styles[id] !== 'undefined' &&
            typeof styling.styles[id][target] !== 'undefined' &&
            typeof styling.styles[id][target][style] !== 'undefined' &&
            typeof styling.styles[id][target][style][extra] !== 'undefined'
        )
            return styling.styles[id][target][style][extra];
        else
            return null;
    } else {
        if (
            typeof styling.styles !== 'undefined' &&
            typeof styling.styles[id] !== 'undefined' &&
            typeof styling.styles[id][target] !== 'undefined' &&
            typeof styling.styles[id][target][style] !== 'undefined'
        )
            return styling.styles[id][target][style];
        else
            return null;
    }

}

const updateStyling = (props: any) => {

    const { setStyles, styling, id, target, toUpdate } = props;

    if (typeof styling.styles[id] === 'undefined') {
        const newData = {
            [id]: {
                [target]: {
                    ...toUpdate
                }
            }
        }

        setStyles({
            ...styling,
            styles: {
                ...styling.styles,
                ...newData
            }
        });

    } else {

        setStyles({
            ...styling,
            styles: {
                ...styling.styles,
                [id]: {
                    ...styling.styles[id],
                    [target]: {
                        ...styling.styles[id][target],
                        ...toUpdate
                    }
                }
            }
        });

    }

}

const ShowStyles = (props: any) => {

    const { styling, setStyles } = props;
    const id = styling.item.id;
    const styleProps = {
        styling, setStyles, id, updateStyling, getStyleValue
    }

    const displayStyles = () => {

        if (styling.item.type === 'ROW') {

            return (<>
                <Width {...styleProps} target='box' />
                <Margin {...styleProps} target='box' />
                <Padding {...styleProps} target='box' />
            </>);

        } else if (styling.item.type === 'ITEM') {

            let options = [];
            switch (styling.item.itemId) {
                // Header / Footer Elements
                case 'search-button':
                case 'clear-filters':
                case 'search-input':
                case 'category-filter':
                case 'add-selected-to-cart-button':
                case 'cart-subtotal':
                case 'product-count':
                case 'pagination':
                case 'attribute-filter':
                case 'cart-widget':
                case 'filter-products-by-attribute':
                case 'filter-products-by-price':
                    options.push(
                        <Width {...styleProps} target='box' />,
                        <Width {...styleProps} target='element' />,
                        <Alignment {...styleProps} target='box' />,
                        <FontSize {...styleProps} target='element' />,
                        <Margin {...styleProps} target='element' />,
                        <Padding {...styleProps} target='element' />
                    );
                    break;
                // Table Elements
                case 'product-image':
                case 'product-name':
                case 'sku':
                case 'in-stock-amount':
                case 'price':
                case 'quantity-input':
                case 'add-to-cart-button':
                case 'combo-variation-dropdown':
                case 'standard-variation-dropdowns':
                case 'product-meta':
                case 'global-attribute':
                case 'short-description':
                    // options.push(
                    //     <FontSize {...styleProps} target='element' />
                    // );
                    // break;
                    // options.push(<></>);
                    break;
                default:
                // options.push(<></>);

            }

            if (options.length > 0)
                return options.map((component: any, key: number) => React.cloneElement(component, { key }));
            else
                return (<>No Styles.</>)
        }
    }

    return (
        <>
            {displayStyles()}
        </>
    )
}

export default ShowStyles;