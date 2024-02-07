<script>
    import {createEventDispatcher} from 'svelte';

    export let el = null;
    export let label = '';
    export let placeholder = null;
    export let items = [];
    export let selected = '';
    export let error = null;

    let id = Math.random().toString(36).substr(2, 9);
    // Sort items by position
    $: items.sort(compare);

    function compare(a, b) {
        if (a.pos < b.pos) return -1;
        if (a.pos > b.pos) return 1;
        return 0;
    }

    const dispatch = createEventDispatcher();

    function onChange() {
        dispatch('change', selected);
    }
</script>

<label for="bookly-rnd-{id}" bind:this={el}>{label}</label>
<div>
    <select id="bookly-rnd-{id}" bind:value="{selected}" on:change="{onChange}">
        {#if placeholder}
            <option value="{placeholder.id}">{placeholder.name}</option>
        {/if}
        {#each items as item}
            {#if !item.hidden}
                <option value="{item.id}">{item.name}</option>
            {/if}
        {/each}
    </select>
</div>
{#if error}
    <div class="bookly-label-error">{error}</div>
{/if}